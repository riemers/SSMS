#include <sourcemod>
#include <sdktools>
#undef REQUIRE_PLUGIN

#define PLUGIN_VERSION "5.1"
new Handle:db = INVALID_HANDLE;			/** Database connection */

public Plugin:myinfo = 
{
        name = "Classy Crash Logger",
        author = "DarthNinja LZ Custom",
        description = "Logs Server Starups to a MySQL database",
        version = PLUGIN_VERSION,
        url = "http://Forums.Alliedmods.net"
};
public OnPluginStart()
{
        openDatabaseConnection()
        createdbtables()
        CreateConVar("sm_crashlogger_version", PLUGIN_VERSION, "Crash Logger", FCVAR_PLUGIN|FCVAR_SPONLY|FCVAR_REPLICATED|FCVAR_NOTIFY);
        logdata()
        RegServerCmd("quit", cmd_Restart);
        RegServerCmd("_restart", cmd_Restart);
}

public Action:cmd_Restart(argCount) {

        decl String:path[PLATFORM_MAX_PATH + 1];
        BuildPath(Path_SM, path, sizeof(path), "data/crashlogger");
        new Handle:file = OpenFile(path, "w");
        WriteFileString(file, "", false);
        CloseHandle(file);

}

openDatabaseConnection()
{
        if (SQL_CheckConfig("crashlogger"))
        {
        new String:error[255]
        db = SQL_Connect("crashlogger",true,error, sizeof(error))
        if (db == INVALID_HANDLE)
        {
        PrintToServer("Failed to connect: %s", error)
        }
        else 
        {
        LogMessage("DatabaseInit (CONNECTED) with db config");
        /* Set codepage to utf8 */

        decl String:query[255];
        Format(query, sizeof(query), "SET NAMES 'utf8'");
        if (!SQL_FastQuery(db, query))
        {
        LogError("Can't select character set (%s)", query);
        }

        /* End of Set codepage to utf8 */
        }
        
        } 
        else 
        {
        LogError("Can't select database");
        }
}
        
createdbtables()
{
        new len = 0;
        decl String:query[2048];
        len += Format(query[len], sizeof(query)-len, "CREATE TABLE IF NOT EXISTS `restarts`");
        len += Format(query[len], sizeof(query)-len, "(`indexnum` INTEGER AUTO_INCREMENT PRIMARY KEY, `timedate` timestamp NULL default CURRENT_TIMESTAMP, `serverid` INTEGER, `manual` );");
        SQL_FastQuery(db, query)
}

logdata()
{

        decl String:path[PLATFORM_MAX_PATH + 1];
        BuildPath(Path_SM, path, sizeof(path), "data/crashlogger");
        if (FileExists(path)) {
                DeleteFile(path);
        } else {
                    new ServerPort;
                    ServerPort = GetConVarInt(FindConVar("hostport"));
                    new String:ServerIp[16];
                    new iIp = GetConVarInt(FindConVar("hostip"));
                    ServerPort = GetConVarInt(FindConVar("hostport"));
                    Format(ServerIp, sizeof(ServerIp), "%i.%i.%i.%i", (iIp >> 24) & 0x000000FF,
                                        (iIp >> 16) & 0x000000FF,
                                        (iIp >>  8) & 0x000000FF,
                                        iIp         & 0x000000FF);
                    new len = 0;
                    decl String:query[2048];
                    len += Format(query[len], sizeof(query)-len, "INSERT INTO restarts (`serverid`) VALUES ((select serverid from servers where port = '%i' and ip = '%s'));", ServerPort, ServerIp);
                    SQL_FastQuery(db, query)
                //LogMessage("INSERT INTO restarts (`timedate`,`serverid`) VALUES ('%i',(select serverid from servers where port = '%i' and ip = '%s'));", time, ServerPort, ServerIp);
        }
}

