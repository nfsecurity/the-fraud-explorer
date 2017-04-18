/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2017-04
 * Revision: v1.0.0-beta
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

        private void Application_Startup(object sender, StartupEventArgs e)
        {
            // Prevent multiple executions

            Common.preventDuplicate();

            try
            {
                // Database initialization

                SQLStorage.DBInitializationChecks();

                // Startup checks

                Common.startupChecks();

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
