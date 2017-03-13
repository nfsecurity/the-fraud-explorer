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
            DLLEmbed.Load("TFE_core.Library.SQLite.Community.CsharpSqlite.dll", "Community.CsharpSqlite.dll");
            DLLEmbed.Load("TFE_core.Library.SQLite.Community.CsharpSqlite.SQLiteClient.dll", "Community.CsharpSqlite.SQLiteClient.dll");
            DLLEmbed.Load("TFE_core.Library.Log4Net.log4net.dll","log4net.dll");

            AppDomain.CurrentDomain.AssemblyResolve += new ResolveEventHandler(CurrentDomain_AssemblyResolve);           
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

        public static string execMode = null;

        private void Application_Startup(object sender, StartupEventArgs e)
        {
            // Prevent multiple executions

            Common.preventDuplicate();

            try
            {
                // Database initialization

                SQLStorage.DBInitializationChecks();

                // Define "msi" or "standalone" mode

                execMode = "msi";

                // Registry preparation

                Common.registryChecks();

                // Start modules

                modulesControl mod = new modulesControl();
                mod.startModules();              
            }
            catch { }
        }

        #endregion

        /// <summary>
        /// Application exiting
        /// </summary>

        #region Application exit

        private void Application_Exit(object sender, ExitEventArgs e) { }

        #endregion      
    }
}
