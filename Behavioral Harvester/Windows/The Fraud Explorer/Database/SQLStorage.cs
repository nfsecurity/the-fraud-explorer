/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2019 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2019-11
 * Revision: v2.0.1-ai
 *
 * Description: SQL Storage
 */

using System;
using Community.CsharpSqlite.SQLiteClient;
using System.IO;
using TFE_core.Config;
using TFE_core.Networking;
using System.Xml.XPath;
using TFE_core.Installer;

namespace TFE_core.Database
{
    class SQLStorage
    {
        /// <summary>
        /// Create DB rutine
        /// </summary>

        #region Create DB rutine

        // Global variables

        static readonly string sqliteFile = Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData) + "\\Software\\endpoint.db3";
        public static string TFE_conn = string.Format("Version=3,uri=file:{0}, Password={1}", @sqliteFile, globalConfigParams.sqlitePassword);      
        public static SqliteConnection generic_connection = new SqliteConnection(TFE_conn);
        
        public void CreateDB()
        {
            try
            {
                // Create database directory

                string dbDirectory = Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData) + "\\Software";
                bool ifExists = System.IO.Directory.Exists(dbDirectory);

                if (!ifExists) System.IO.Directory.CreateDirectory(dbDirectory);

                // Name, Path and password

                string cs = string.Format("Version=3,uri=file:{0}", @sqliteFile);
                SqliteConnection connection = new SqliteConnection(cs);
                connection.Open();
                System.Data.IDbCommand encryption = connection.CreateCommand();
                encryption.CommandText = "pragma hexkey='"+ globalConfigParams.sqlitePassword + "'";
                encryption.ExecuteNonQuery();

                // Database structure

                String[] sql_structure = {"create table config (parameter varchar(50), value varchar(100))"};

                foreach (string element in sql_structure)
                {
                    SqliteCommand command = new SqliteCommand(element, connection);
                    command.ExecuteNonQuery();
                }

                // Population of config table

                SqliteCommand insertSQL = new SqliteCommand("insert into config (parameter, value) VALUES ('textAnalytics', '" + globalConfigParams.textAnalytics + "')", connection);
                insertSQL.ExecuteNonQuery();
                
                insertSQL = new SqliteCommand("insert into config (parameter, value) VALUES ('heartbeat', '" + globalConfigParams.heartbeat + "')", connection);
                insertSQL.ExecuteNonQuery();

                insertSQL = new SqliteCommand("insert into config (parameter, value) VALUES ('uniqueguid', '" + UNIQUEGUID_VALUE() + "')", connection);
                insertSQL.ExecuteNonQuery();

                insertSQL = new SqliteCommand("insert into config (parameter, value) VALUES ('mainServer', '" + globalConfigParams.serverAddress + "')", connection);
                insertSQL.ExecuteNonQuery();

                insertSQL = new SqliteCommand("insert into config (parameter, value) VALUES ('analyticsServer', '" + globalConfigParams.serverIP + "')", connection);
                insertSQL.ExecuteNonQuery();

                insertSQL = new SqliteCommand("insert into config (parameter, value) VALUES ('aesKey', '" + globalConfigParams.aesKey + "')", connection);
                insertSQL.ExecuteNonQuery();

                insertSQL = new SqliteCommand("insert into config (parameter, value) VALUES ('excludedApps', '" + globalConfigParams.excludedApps + "')", connection);
                insertSQL.ExecuteNonQuery();

                insertSQL = new SqliteCommand("insert into config (parameter, value) VALUES ('aesIV', '" + globalConfigParams.aesIV + "')", connection);
                insertSQL.ExecuteNonQuery();

                insertSQL = new SqliteCommand("insert into config (parameter, value) VALUES ('serverPassword', '" + globalConfigParams.serverPassword + "')", connection);
                insertSQL.ExecuteNonQuery();

                insertSQL = new SqliteCommand("insert into config (parameter, value) VALUES ('harvesterVersion', '" + globalConfigParams.harvesterVersion + "')", connection);
                insertSQL.ExecuteNonQuery();

                insertSQL = new SqliteCommand("insert into config (parameter, value) VALUES ('agentPostfix', '" + globalConfigParams.agentPostfix + "')", connection);
                insertSQL.ExecuteNonQuery();

                insertSQL = new SqliteCommand("insert into config (parameter, value) VALUES ('textPort', '" + globalConfigParams.textPort + "')", connection);
                insertSQL.ExecuteNonQuery();
                
                connection.Close();

                Filesystem.SetFullFilePermissions(sqliteFile);
            }
            catch (Exception ex)
            {
                Filesystem.WriteLog("ERROR : Exception trown in database creation : " + ex);
            }
        }

        #endregion

        /// <summary>
        /// Get parameters from DB
        /// </summary>

        #region Get parameters from DB

        public static string RetrievePar(string parameter)
        {
            try
            {
                generic_connection.Open();
                string command = "select value from config where parameter='" + parameter + "';";
                SqliteCommand cmd = new SqliteCommand(command, generic_connection);
                string result = cmd.ExecuteScalar().ToString();
                generic_connection.Close();
                return result;
            }
            catch (Exception ex)
            {
                Filesystem.WriteLog("ERROR : Exception trown while retrieving database data : " + ex);
                return null;
            }
        }

        #endregion

        /// <summary>
        /// Set parameters in DB
        /// </summary>

        #region Set parameters in DB

        public static void ModifyPar(string command, string variable_with_value, string uniqueID)
        {
            try
            {
                string[] variable_and_parameter = variable_with_value.Split(' ');
                string variable = variable_and_parameter[0];
                string value = variable_and_parameter[variable_and_parameter.Length - 1];

                generic_connection.Open();
                string SQLcommand = "update config set value='" + value + "' where parameter='" + variable + "';";
                SqliteCommand cmd = new SqliteCommand(SQLcommand, generic_connection);
                cmd.ExecuteNonQuery();
                generic_connection.Close();

                // Inform that the command was received

                Network.SendData(variable + " changed!", command, uniqueID, 1);
            }
            catch (Exception ex)
            {
                Filesystem.WriteLog("ERROR : Exception trown while modifying database data : " + ex);
            }
        }

        #endregion

        /// <summary>
        /// Database initialization checks
        /// </summary>

        #region Database initialization checks

        public static void DBInitializationChecks()
        {
            try
            {
                string sqliteFile = Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData) + "\\Software\\endpoint.db3";
                string MSIConfigFilePath = Environment.GetFolderPath(Environment.SpecialFolder.CommonApplicationData) + "\\Software\\configApp.xml";

                if (!File.Exists(sqliteFile) && File.Exists(MSIConfigFilePath))
                {
                    // Read MSI config file

                    XPathDocument configMSI = new XPathDocument(MSIConfigFilePath);
                    XPathNavigator navConfigMSI = configMSI.CreateNavigator();

                    globalConfigParams.serverAddress = InstallerClass.DecRijndaelMSI(navConfigMSI.SelectSingleNode("/ConfigParameters/address").ToString()).Replace("\0", String.Empty);
                    globalConfigParams.serverIP = InstallerClass.DecRijndaelMSI(navConfigMSI.SelectSingleNode("/ConfigParameters/ip").ToString()).Replace("\0", String.Empty);
                    globalConfigParams.textAnalytics = InstallerClass.DecRijndaelMSI(navConfigMSI.SelectSingleNode("/ConfigParameters/pcenabled").ToString()).Replace("\0", String.Empty);
                    globalConfigParams.aesKey = InstallerClass.DecRijndaelMSI(navConfigMSI.SelectSingleNode("/ConfigParameters/cryptkey").ToString()).Replace("\0", String.Empty);
                    globalConfigParams.aesIV = InstallerClass.DecRijndaelMSI(navConfigMSI.SelectSingleNode("/ConfigParameters/cryptkey").ToString()).Replace("\0", String.Empty);
                    globalConfigParams.serverPassword = InstallerClass.DecRijndaelMSI(navConfigMSI.SelectSingleNode("/ConfigParameters/srvpwd").ToString()).Replace("\0", String.Empty);
                    globalConfigParams.excludedApps = InstallerClass.DecRijndaelMSI(navConfigMSI.SelectSingleNode("/ConfigParameters/apps").ToString()).Replace("\0", String.Empty);

                    SQLStorage db = new SQLStorage();
                    db.CreateDB();

                    Filesystem.WriteLog("INFO : Internal database created from MSI config file");
                }
                else if (!File.Exists(sqliteFile) && !File.Exists(MSIConfigFilePath))
                {
                    Filesystem.WriteLog("ERROR : Internal database and MSI config file doesn't exist, please run MSI Installer");
                    Environment.Exit(0);
                }
                else Filesystem.WriteLog("INFO : User DB3 database found, continue");
            }
            catch (Exception ex)
            {
                Filesystem.WriteLog("ERROR : Exception trown in Database check procedure : " + ex);
            }
        }

        #endregion

        /// <summary>
        /// Application database references
        /// </summary>

        #region Application database references

        // Get machine unique identification

        public static string UNIQUEGUID_VALUE()
        {
            return "_" + GetMachineGUID() + globalConfigParams.agentPostfix;
        }

        // Get Machine ID for unique identification helper

        public static string GetMachineGUID()
        {
            Guid MachineGuid;
            MachineGuid = Guid.NewGuid();
            return MachineGuid.ToString().ToLower().Substring(0, 7);
        }

        #endregion
    }
}