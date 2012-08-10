#define DEFAULT_CACHING_TIME "30.0"

#include <sourcemod>
#include <sdktools>

#include <colors>

#define PLUGIN_NAME "[SSMS] Server Redirect"
#define PLUGIN_AUTHOR "1Swat2KillThemAll"
#define PLUGIN_DESCRIPTION ""
#define PLUGIN_VERSION "1.0 (GNU/GPLv3)"
#define PLUGIN_URL ""
public Plugin:myinfo = {
	name = PLUGIN_NAME,
	author = PLUGIN_AUTHOR,
	description = PLUGIN_DESCRIPTION,
	version = PLUGIN_VERSION,
	url = PLUGIN_URL
};

new Handle:g_hServerName = INVALID_HANDLE,
	Handle:g_hServerIP = INVALID_HANDLE,
	Handle:g_hServerMap = INVALID_HANDLE,
	Handle:g_hServerPlayers = INVALID_HANDLE,
	Handle:g_hServerBots = INVALID_HANDLE,
	Handle:g_hServerMaxPlayers = INVALID_HANDLE;

new Handle:g_hDatabase = INVALID_HANDLE;

new Handle:g_hCvEnabled, bool:g_CvEnabled;
new Handle:g_hCvCachingInterval, Float:g_CvCachingInterval;
new Handle:g_hCvAdvertInterval, Float:g_CvAdvertInterval;

new Handle:g_hAdvertTimer;

new String:g_HostNameTag[64];

new String:g_GameType[32];
new String:g_IP[32];
new g_Port;

public OnPluginStart() {
	InitVersionCvar("ssms_server_redirect", PLUGIN_NAME, PLUGIN_VERSION);
	g_CvEnabled = InitCvar(g_hCvEnabled, OnConVarChanged, "sm_ssms_server_redirect_enabled", "1", "Whether this plugin should be enabled", FCVAR_DONTRECORD, true, 0.0, true, 1.0);
	g_CvCachingInterval = InitCvar(g_hCvCachingInterval, OnConVarChanged, "sm_ssms_server_redirect_caching_interval", DEFAULT_CACHING_TIME, "Time in seconds between updating the cache.", FCVAR_DONTRECORD, true, 1.0, false);
	g_CvAdvertInterval = InitCvar(g_hCvAdvertInterval, OnConVarChanged, "sm_ssms_server_redirect_advert_interval", "30.0", "Time in seconds between advertisments. (0 disables)", FCVAR_DONTRECORD, true, 0.0, false);

	GetGameFolderName(g_GameType, sizeof(g_GameType));
	new ip = GetConVarInt(FindConVar("hostip")),
		bitmask = 0x000000FF;
	Format(g_IP, sizeof(g_IP), "%i.%i.%i.%i", (ip >> 24) & bitmask, (ip >> 16) & bitmask, (ip >> 8) & bitmask, ip & bitmask);
	g_Port = GetConVarInt(FindConVar("hostport"));

	g_hServerName = CreateArray(64);
	g_hServerIP = CreateArray(64);
	g_hServerMap = CreateArray(64);
	g_hServerPlayers = CreateArray();
	g_hServerBots = CreateArray();
	g_hServerMaxPlayers = CreateArray();

	LoadTranslations("ssms_server_redirect.phrases");

	Format(g_HostNameTag, sizeof(g_HostNameTag), "%t", "Strip From Hostname", LANG_SERVER);

	SQL_TConnect(SQLT_Connect, "ssms");

	g_hAdvertTimer = CreateTimer(g_CvAdvertInterval, ShowAdvert, _, TIMER_REPEAT);

	RegConsoleCmd("sm_servers", ConCmd_Servers, "");
}

public OnMapStart() {
	if (g_hDatabase == INVALID_HANDLE) {
		SQL_TConnect(SQLT_Connect, "ssms");
	}
}

public Action:ConCmd_Servers(client, argc) {
	if (g_CvEnabled) {
		ShowServerList(client);
	}
}

ShowServerList(client) {
	new Handle:menu = CreateMenu(MenuHandler_ShowServerList);

	SetMenuTitle(menu, "%t", "Server List Title", client);

	decl String:info[64],
		String:display[512];

	new size = GetArraySize(g_hServerName);
	for (new i = 0; i < size; i++) {
		GetArrayString(g_hServerIP, i, info, sizeof(info));
		Format(display, sizeof(display), "%t", "Server List Entry", client);

		ReplaceTags(display, sizeof(display), i, true);

		AddMenuItem(menu, info, display);
	}

	if (size == 0) {
		Format(display, sizeof(display), "%t", "No servers available", client);
		AddMenuItem(menu, "", display, ITEMDRAW_DISABLED);
	}

	DisplayMenu(menu, client, MENU_TIME_FOREVER);
}

public MenuHandler_ShowServerList(Handle:menu, MenuAction:action, param1, param2) {
	switch (action) {
		case MenuAction_Select:
		{
			decl String:ip[64];
			ip[0] = '\0';
			GetMenuItem(menu, param2, ip, sizeof(ip));

			if (StrEqual(ip, "")) {
				return;
			}

			DisplayAskConnectBox(param1, 20.0, ip);
		}
		case MenuAction_Cancel:
		{
		}
		case MenuAction_End:
		{
			CloseHandle(menu);
		}
	}
}

public Action:RetrieveServerInfo(Handle:timer, any:data) {
	if (g_hDatabase == INVALID_HANDLE) {
		return;
	}

	decl String:query[512];

	Format(query, sizeof(query),
		"SELECT `servername`, CONCAT(`ip`, ':', `port`), `currentmap`, `currentplayers`, `currentbots`, `maxplayers` \
		FROM `servers` \
		WHERE \
			`type` = '%s' AND \
			`showmotd` = 'yes' AND \
			`retries` <= '5' AND \
			(`ip` <> '%s' OR `port` <> '%i') \
		ORDER BY servername ASC\
		;",
		g_GameType,
		g_IP,
		g_Port
	);

	SQL_TQuery(g_hDatabase, SQLT_RetrieveServerInfo,
		query,
		data, DBPrio_Low
	);
}

public SQLT_Connect(Handle:owner, Handle:hndl, const String:error[], any:data) {
	if (hndl == INVALID_HANDLE) {
		LogError("Couldn't connect to database: %s", error);
		g_hDatabase = INVALID_HANDLE;
	}
	else {
		g_hDatabase = hndl;
		RetrieveServerInfo(INVALID_HANDLE, true);
	}
}

public SQLT_RetrieveServerInfo(Handle:owner, Handle:hndl, const String:error[], any:data) {
	if (hndl == INVALID_HANDLE) {
		LogError("Couldn't execute query: %s", error);
		CloseHandle2(g_hDatabase);
	}
	else {
		new Handle:hServerName = CreateArray(64),
			Handle:hServerIP = CreateArray(64),
			Handle:hServerMap = CreateArray(64),
			Handle:hServerPlayers = CreateArray(),
			Handle:hServerBots = CreateArray(),
			Handle:hServerMaxPlayers = CreateArray();

		decl String:buffer[64];
		while (SQL_FetchRow(hndl)) {
			SQL_FetchString(hndl, 0, buffer, sizeof(buffer));
			ReplaceString(buffer, sizeof(buffer), g_HostNameTag, "", false);
			PushArrayString(hServerName, buffer);

			SQL_FetchString(hndl, 1, buffer, sizeof(buffer));
			PushArrayString(hServerIP, buffer);

			SQL_FetchString(hndl, 2, buffer, sizeof(buffer));
			PushArrayString(hServerMap, buffer);

			PushArrayCell(hServerPlayers, SQL_FetchInt(hndl, 3));

			PushArrayCell(hServerBots, SQL_FetchInt(hndl, 4));

			PushArrayCell(hServerMaxPlayers, SQL_FetchInt(hndl, 5));
		}

		CloseHandle2(g_hServerName);
		CloseHandle2(g_hServerIP);
		CloseHandle2(g_hServerMap);
		CloseHandle2(g_hServerPlayers);
		CloseHandle2(g_hServerBots);
		CloseHandle2(g_hServerMaxPlayers);

		g_hServerName = hServerName;
		g_hServerIP = hServerIP;
		g_hServerMap = hServerMap;
		g_hServerPlayers = hServerPlayers;
		g_hServerBots = hServerBots;
		g_hServerMaxPlayers = hServerMaxPlayers;

		if (data) {
			CreateTimer(g_CvCachingInterval, RetrieveServerInfo, data);
		}
	}
}

public Action:ShowAdvert(Handle:timer, any:data) {
	static index = -1;

	new max_adverts = GetArraySize(g_hServerName);

	if (max_adverts == 0) {
		return;
	}

	if (++index >= max_adverts) {
		index = 0;
	}

	decl String:advert[512];
	Format(advert, sizeof(advert), "%t", "Advertisment", LANG_SERVER);
	ReplaceTags(advert, sizeof(advert), index);

	CPrintToChatAll(advert);
}

public OnConVarChanged(Handle:cvar, const String:oldVal[], const String:newVal[]) {
	if (cvar == g_hCvEnabled) {
		g_CvEnabled = bool:StringToInt(newVal);
	}
	else if (cvar == g_hCvCachingInterval) {
		g_CvCachingInterval = StringToFloat(newVal);
	}
	else if (cvar == g_hCvAdvertInterval) {
		g_CvCachingInterval = StringToFloat(newVal);

		CloseHandle2(g_hAdvertTimer);

		if (g_CvAdvertInterval > 0.0) {
			g_hAdvertTimer = CreateTimer(g_CvAdvertInterval, ShowAdvert, _, TIMER_REPEAT);
		}
	}
}

ReplaceTags(String:text[], maxlength, index, bool:strip_colours = false) {
	decl String:buffer[64];

	if (strip_colours) {
		CRemoveTags(text, maxlength);
	}

	if (StrContains(text, "{NAME}", false) != -1) {
		GetArrayString(g_hServerName, index, buffer, sizeof(buffer));
		ReplaceString(text, maxlength, "{NAME}", buffer, false);
	}

	if (StrContains(text, "{IP}", false) != -1) {
		GetArrayString(g_hServerIP, index, buffer, sizeof(buffer));
		ReplaceString(text, maxlength, "{IP}", buffer, false);
	}

	if (StrContains(text, "{MAP}", false) != -1) {
		GetArrayString(g_hServerMap, index, buffer, sizeof(buffer));
		ReplaceString(text, maxlength, "{MAP}", buffer, false);
	}

	if (StrContains(text, "{PLAYERS}", false) != -1) {
		Format(buffer, sizeof(buffer), "%i", GetArrayCell(g_hServerPlayers, index));
		ReplaceString(text, maxlength, "{PLAYERS}", buffer, false);
	}

	if (StrContains(text, "{BOTS}", false) != -1) {
		Format(buffer, sizeof(buffer), "%i", GetArrayCell(g_hServerBots, index));
		ReplaceString(text, maxlength, "{BOTS}", buffer, false);
	}

	if (StrContains(text, "{MAXPLAYERS}", false) != -1) {
		Format(buffer, sizeof(buffer), "%i", GetArrayCell(g_hServerMaxPlayers, index));
		ReplaceString(text, maxlength, "{MAXPLAYERS}", buffer, false);
	}
}

/**
 * \brief Creates a plugin version console variable.
 *
 * \return									Whether creating the console variable was successful
 * \error									Convar name is blank or is the same as an existing console command
 */
stock InitVersionCvar(
	const String:cvar_name[],				///<! [in] The console variable's name (sm_<name>_version)
	const String:plugin_name[],				///<! [in] The plugin's name
	const String:plugin_version[],			///<! [in] The plugin's version
	additional_flags = 0					///<! [in] additional FCVAR_* flags  (default: FCVAR_NOTIFY | FCVAR_SPONLY | FCVAR_DONTRECORD)
) {
	if (StrEqual(cvar_name, "") || StrEqual(plugin_name, "")) {
		return false;
	}

	new cvar_name_len = strlen(cvar_name) + 12,
		descr_len = strlen(cvar_name) + 20;
	decl String:name[cvar_name_len],
		String:descr[descr_len];

	Format(name, cvar_name_len, "sm_%s_version", cvar_name);
	Format(descr, descr_len, "\"%s\" - version number", plugin_name);

	new Handle:cvar = FindConVar(name),
		flags = FCVAR_NOTIFY | FCVAR_DONTRECORD | additional_flags;

	if (cvar != INVALID_HANDLE) {
		SetConVarString(cvar, plugin_version);
		SetConVarFlags(cvar, flags);
	}
	else {
		cvar = CreateConVar(name, plugin_version, descr, flags);
	}

	if (cvar != INVALID_HANDLE) {
		CloseHandle(cvar);
		return true;
	}

	LogError("Couldn't create version console variable \"%s\".", name);
	return false;
}

/**
 * \brief Creates a new console variable and hooks it to the specified OnConVarChanged: callback.
 *
 * This function attempts to deduce from the default value what type of data (int, float)
 * is supposed to be stored in the console variable, and returns its value accordingly.
 * (Its type can also be manually specified.) Alternatively one could opt to let the
 * ConVarChanged: callback do the initialisation. This is however prone to error;
 * should CreateConVar() fail, the callback is never fired.
 *
 * \return									Context sensitive; check detailed description
 * \error									Callback is invalid, or convar name is blank or is the same as an existing console command
 */
stock any:InitCvar(
	&Handle:cvar,							///<! [out] A handle to the newly created convar. If the convar already exists, a handle to it will still be returned.
	ConVarChanged:callback,					///<! [in] Callback function called when the convar's value is modified.
	const String:name[],					///<! [in] Name of new convar
	const String:defaultValue[],			///<! [in] String containing the default value of new convar
	const String:description[] = "",		///<! [in] Optional description of the convar
	flags = 0,								///<! [in] Optional bitstring of flags determining how the convar should be handled. See FCVAR_* constants for more details
	bool:hasMin = false,					///<! [in] Optional boolean that determines if the convar has a minimum value
	Float:min = 0.0,						///<! [in] Minimum floating point value that the convar can have if hasMin is true
	bool:hasMax = false,					///<! [in] Optional boolean that determines if the convar has a maximum value
	Float:max = 0.0,						///<! [in] Maximum floating point value that the convar can have if hasMax is true
	type = -1								///<! [in] Return / initialisation type
) {
	cvar = CreateConVar(name, defaultValue, description, flags, hasMin, min, hasMax, max);
	if (cvar != INVALID_HANDLE) {
		HookConVarChange(cvar, callback);
	}
	else {
		LogMessage("Couldn't create console variable \"%s\", using default value \"%s\".", name, defaultValue);
	}

	if (type < 0 || type > 3) {
		type = 1;
		new len = strlen(defaultValue);
		for (new i = 0; i < len; i++) {
			if (defaultValue[i] == '.') {
				type = 2;
			}
			else if (IsCharNumeric(defaultValue[i])) {
				continue;
			}
			else {
				type = 0;
				break;
			}
		}
	}

	if (type == 1) {
		return cvar != INVALID_HANDLE ? GetConVarInt(cvar) : StringToInt(defaultValue);
	}
	else if (type == 2) {
		return cvar != INVALID_HANDLE ? GetConVarFloat(cvar) : StringToFloat(defaultValue);
	}
	else if (cvar != INVALID_HANDLE && type == 3) {
		Call_StartFunction(INVALID_HANDLE, callback);
		Call_PushCell(cvar);
		Call_PushString("");
		Call_PushString(defaultValue);
		Call_Finish();

		return true;
	}

	return 0;
}

stock CloseHandle2(&Handle:hndl) {
	if (hndl != INVALID_HANDLE) {
		CloseHandle(hndl);
		hndl = INVALID_HANDLE;
	}
}
