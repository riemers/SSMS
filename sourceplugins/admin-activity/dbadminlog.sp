/**
* Taken most stuff purely from MySCAL which came from TSCDan 	
* see https://forums.alliedmods.net/showthread.php?t=73119 for all the credits due..
*/

#pragma semicolon 1
#include <sourcemod>

#define MS_VERSION "1.0"

new Handle:hDatabase = INVALID_HANDLE;

public Plugin:myinfo = 
{
	name = "MySQL Admin Logging only",
	author = "Snelvuur",
	description = "Store only admin commands in a database",
	version = MS_VERSION,
	url = "http://www.lethal-zone.eu"
};

public OnPluginStart()
{
	CreateConVar("sm_dbadmin_version", MS_VERSION, _, FCVAR_PLUGIN|FCVAR_NOTIFY|FCVAR_REPLICATED|FCVAR_SPONLY);
	StartSQL();
}

StartSQL()
{
	SQL_TConnect(GotDatabase);
}

public GotDatabase(Handle:owner, Handle:hndl, const String:error[], any:data)
{
	if (hndl == INVALID_HANDLE)
	{
		LogError("[dbadminlogs] Database failure: %s", error);
	}
	else
	{
                hDatabase = hndl;
	        LogMessage("Database Init (CONNECTED)");					// Message Console
	}
}

public Action:OnLogAction(Handle:source, Identity:ident, client, target, const String:message[])
{
	// If there is no client or they're not an admin, we don't care.
	if (client < 1 || GetUserAdmin(client) == INVALID_ADMIN_ID)
	{
		return Plugin_Continue;
	}
	
	decl String:logtag[64];
	
	// At the moment extensions can't be passed through here yet,  so we only bother with plugins, and use "SM" for anything else.
	if (ident == Identity_Plugin)
	{
		GetPluginFilename(source, logtag, sizeof(logtag));
	} 
	else 
	{
		strcopy(logtag, sizeof(logtag), "SM");
	}

	decl String:steamid[32];
	GetClientAuthString(client, steamid, sizeof(steamid));

	new quotedLen = (strlen(message) * 2) + 1;
	decl String:quotedMessage[quotedLen];

	SQL_EscapeString(hDatabase, message, quotedMessage, quotedLen);

	if (hDatabase == INVALID_HANDLE)
	{
		LogError("[DBAdm] Database Error");
		return Plugin_Handled;
	}
		
	decl String:query[512];									// For Query

        new ServerPort;
        ServerPort = GetConVarInt(FindConVar("hostport"));
        new String:ServerIp[16];
        new iIp = GetConVarInt(FindConVar("hostip"));
        ServerPort = GetConVarInt(FindConVar("hostport"));
        Format(ServerIp, sizeof(ServerIp), "%i.%i.%i.%i", (iIp >> 24) & 0x000000FF,
                (iIp >> 16) & 0x000000FF,
                (iIp >>  8) & 0x000000FF,
                iIp         & 0x000000FF);
	decl String:name[33];
	GetClientName(client, name, 33);

	Format(query, sizeof(query), 
	"INSERT INTO sm_logging (serverid, steamid, logtag, message, name) VALUES ((select serverid from servers where port = '%i' and ip = '%s'), '%s', '%s', '%s', '%s')", ServerPort, ServerIp, steamid, logtag, quotedMessage, name);
	
	SQL_TQuery(hDatabase, T_RanLogging, query);	
		
	// Error Checking
	//PrintToChatAll("\x04[DBAdm]\x03 SN: %i, Steam ID: %s, LogTag: %s, Ip: %s", ServerPort, steamid, logtag, ServerIp);
	//PrintToChatAll("\x04[DBAdm]\x03 Query: %s", quotedMessage);
	//LogMessage(query);

	// Stop from running twice
	return Plugin_Handled;
}

public T_RanLogging(Handle:owner2, Handle:hndl2, const String:error[], any:data)
{
        if(!StrEqual("", error))
	{
	//	LogError("[DBAdm] Error Returning Results: %s", error);
		PrintToChatAll("\x04[DBAdm]\x03 Error: %s", error);
		return;
	}
	
	if(hndl2 == INVALID_HANDLE || !SQL_GetRowCount(hndl2))
	{
         //       LogError("[DBAdm] Cannot find Server ID in database or there are no configs for the server");
                return;
	}

        // Error CheckingLogAction(0, -1, "[DBAdm] completed!");

	CloseHandle(hndl2);
	CloseHandle(owner2);
}
