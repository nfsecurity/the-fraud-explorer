/*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2017-04
 * Revision: v0.9.9-beta
 *
 * Description: SQL Storage
 */

using System;
using Community.CsharpSqlite.SQLiteClient;
using System.IO;
using TFE_core.Config;
using TFE_core.Networking;

namespace TFE_core.Database
{
    class SQLStorage
    {
        /// <summary>
        /// Create DB rutine
        /// </summary>

        #region Create DB rutine

        // Global variables

        public static string TFE_conn = string.Format("Version=3,uri=file:{0}, Password={1}", @Settings.sqlFile(), Initialization.parametersFromBinary("sqlitePassword"));
        public static SqliteConnection generic_connection = new SqliteConnection(TFE_conn);
        
        public void createDB()
        {
            try
            {
                // Name, Path and password
               
                string cs = string.Format("Version=3,uri=file:{0}", @Settings.sqlFile());
                SqliteConnection connection = new SqliteConnection(cs);
                connection.Open();
                System.Data.IDbCommand encryption = connection.CreateCommand();
                encryption.CommandText = "pragma hexkey='"+ Initialization.parametersFromBinary("sqlitePassword") + "'";
                encryption.ExecuteNonQuery();

                // Database structure

                String[] sql_structure = {"create table config (parameter varchar(50), value varchar(100))"};

                foreach (string element in sql_structure)
                {
                    SqliteCommand command = new SqliteCommand(element, connection);
                    command.ExecuteNonQuery();
                }

                // Population of config table              

                SqliteCommand insertSQL = new SqliteCommand("insert into config (parameter, value) VALUES ('" + Settings.TAFLAG + "', '"+ Initialization.parametersFromBinary(Settings.TAFLAG) + "')", connection);
                insertSQL.ExecuteNonQuery();

                insertSQL = new SqliteCommand("insert into config (parameter, value) VALUES ('" + Settings.HEARTBEAT + "', '" + Initialization.parametersFromBinary(Settings.HEARTBEAT) + "')", connection);
                insertSQL.ExecuteNonQuery();

                insertSQL = new SqliteCommand("insert into config (parameter, value) VALUES ('" + Settings.UNIQUEGUID + "', '" + Settings.UNIQUEGUID_VALUE() + "')", connection);
                insertSQL.ExecuteNonQuery();

                insertSQL = new SqliteCommand("insert into config (parameter, value) VALUES ('" + Settings.SRFLAG + "', '" + Initialization.parametersFromBinary(Settings.SRFLAG) + "')", connection);
                insertSQL.ExecuteNonQuery();

                insertSQL = new SqliteCommand("insert into config (parameter, value) VALUES ('" + Settings.ANFLAG + "', '" + Initialization.parametersFromBinary(Settings.ANFLAG) + "')", connection);
                insertSQL.ExecuteNonQuery();

                insertSQL = new SqliteCommand("insert into config (parameter, value) VALUES ('" + Settings.AESKEYFLAG + "', '" + Initialization.parametersFromBinary(Settings.AESKEYFLAG) + "')", connection);
                insertSQL.ExecuteNonQuery();

                insertSQL = new SqliteCommand("insert into config (parameter, value) VALUES ('" + Settings.AESIVFLAG + "', '" + Initialization.parametersFromBinary(Settings.AESIVFLAG) + "')", connection);
                insertSQL.ExecuteNonQuery();

                insertSQL = new SqliteCommand("insert into config (parameter, value) VALUES ('" + Settings.EXEFLAG + "', '" + Initialization.parametersFromBinary(Settings.EXEFLAG) + "')", connection);
                insertSQL.ExecuteNonQuery();

                insertSQL = new SqliteCommand("insert into config (parameter, value) VALUES ('" + Settings.SRPWDFLAG + "', '" + Initialization.parametersFromBinary(Settings.SRPWDFLAG) + "')", connection);
                insertSQL.ExecuteNonQuery();

                insertSQL = new SqliteCommand("insert into config (parameter, value) VALUES ('" + Settings.HVERFLAG + "', '" + Initialization.parametersFromBinary(Settings.HVERFLAG) + "')", connection);
                insertSQL.ExecuteNonQuery();

                insertSQL = new SqliteCommand("insert into config (parameter, value) VALUES ('" + Settings.APOSTFIXFLAG + "', '" + Initialization.parametersFromBinary(Settings.APOSTFIXFLAG) + "')", connection);
                insertSQL.ExecuteNonQuery();

                insertSQL = new SqliteCommand("insert into config (parameter, value) VALUES ('" + Settings.REGFLAG + "', '" + Initialization.parametersFromBinary(Settings.REGFLAG) + "')", connection);
                insertSQL.ExecuteNonQuery();

                insertSQL = new SqliteCommand("insert into config (parameter, value) VALUES ('" + Settings.TPORTFLAG + "', '" + Initialization.parametersFromBinary(Settings.TPORTFLAG) + "')", connection);
                insertSQL.ExecuteNonQuery();

                connection.Close();
            }
            catch { }
        }

        #endregion

        /// <summary>
        /// Get parameters from DB
        /// </summary>

        #region Get parameters from DB

        public static string retrievePar(string parameter)
        {
            if (!File.Exists(Settings.sqlFile()))
            {
                SQLStorage db = new SQLStorage(); 
                db.createDB();
            }

            generic_connection.Open();
            string command = "select value from config where parameter='"+parameter+"';";
            SqliteCommand cmd = new SqliteCommand(command, generic_connection);
            string result = cmd.ExecuteScalar().ToString();
            generic_connection.Close();
            return result;
        }

        #endregion

        /// <summary>
        /// Set parameters in DB
        /// </summary>

        #region Set parameters in DB

        public static void modifyPar(string command, string variable_with_value, string uniqueID)
        {
            if (!File.Exists(Settings.sqlFile()))
            {
                SQLStorage db = new SQLStorage();
                db.createDB();
            }

            string[] variable_and_parameter = variable_with_value.Split(' ');
            string variable = variable_and_parameter[0];
            string value = variable_and_parameter[variable_and_parameter.Length - 1];

            generic_connection.Open();
            string SQLcommand = "update config set value='"+value+"' where parameter='" + variable + "';";
            SqliteCommand cmd = new SqliteCommand(SQLcommand, generic_connection);
            cmd.ExecuteNonQuery();
            generic_connection.Close();

            // Inform that the command was received

            Network.SendData(variable + " changed!", command, uniqueID, 1);
        }

        #endregion

        /// <summary>
        /// Database initialization checks
        /// </summary>

        #region Database initialization checks

        public static void DBInitializationChecks()
        {
            if (!File.Exists(Settings.sqlFile()))
            {
                SQLStorage db = new SQLStorage();
                db.createDB();
            }
        }

        #endregion
    }
}
