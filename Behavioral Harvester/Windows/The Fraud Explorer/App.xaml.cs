/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2021 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Author: jrios@nofraud.la
 * Version code-name: nemesis
 *
 * Description: Main Application
 */

using System;
using System.Windows;
using TFE_core.Database;
using TFE_core.Config;

namespace TFE_core
{ 
    public partial class App : Application
    {
        /// <summary>
        /// Embed DLL's attached to the executable
        /// </summary>
 
        #region Embed DLL's 

        public App() 
        {
            try
            {
                DLLEmbed.Load("TFE_core.Library.SQLite.Community.CsharpSqlite.dll", "Community.CsharpSqlite.dll");
                DLLEmbed.Load("TFE_core.Library.SQLite.Community.CsharpSqlite.SQLiteClient.dll", "Community.CsharpSqlite.SQLiteClient.dll");
                DLLEmbed.Load("TFE_core.Library.Log4Net.log4net.dll", "log4net.dll");

                AppDomain.CurrentDomain.AssemblyResolve += new ResolveEventHandler(CurrentDomain_AssemblyResolve);

                Filesystem.WriteLog("INFO : DLLs embedded in the application");
            }
            catch (Exception ex)
            {
                Filesystem.WriteLog("ERROR : DLLs embedding error : " + ex);
            }
        }
      
        static System.Reflection.Assembly CurrentDomain_AssemblyResolve(object sender, ResolveEventArgs args)
        {
            return DLLEmbed.Get(args.Name);
        }

        #endregion

        /// <summary>
        /// Application starting method
        /// </summary>

        #region Application start

        private void Application_Startup(object sender, StartupEventArgs e)
        {
            Filesystem.WriteLog("INFO : Application started");

            // Prevent multiple executions

            Common.PreventDuplicate();

            try
            {
                // Argument passing at execution time

                var commandLineArgs = e.Args;

                if (e.Args.Length != 0) Common.StartupChecks(commandLineArgs[0]);
                else Common.StartupChecks("smoothrun");

                // Database initialization

                SQLStorage.DBInitializationChecks();

                // Start modules

                ModulesControl mod = new ModulesControl();
                mod.StartModules();
                Filesystem.WriteLog("INFO : Modules started successfully");
            }
            catch (Exception ex)
            {
                Filesystem.WriteLog("ERROR : Exception trown in Application Startup : " + ex);
            }
        }

        #endregion

        /// <summary>
        /// Application exiting
        /// </summary>

        #region Application exit

        private void Application_Exit(object sender, ExitEventArgs e)
        {
            Filesystem.WriteLog("INFO : Application closed");
        }

        #endregion      
    }
}