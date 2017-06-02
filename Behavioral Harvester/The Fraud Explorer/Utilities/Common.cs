/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2017-06
 * Revision: v1.0.1-beta
 *
 * Description: Operating system
 */

using System;
using System.Linq;
using TFE_core.Database;

namespace TFE_core.Config
{
    class Common
    {
        /// <summary>
        /// Operating system version
        /// </summary>

        #region Operating system version

        public static string OSVersion()
        {
            return Environment.OSVersion.Version.ToString()[0] + "." + Environment.OSVersion.Version.ToString()[2];
        }

        #endregion

        /// <summary>
        /// Filesystem directory preparation
        /// </summary>

        #region Filesystem directory preparation

        public static string SetAndCheckDir(string Directory)
        {
            string DirectoryForCheckOrCreate = null;
            switch (Directory)
            {
                case "ExecutablePath":
                    DirectoryForCheckOrCreate = Settings.SoftwareBaseDir;
                    break;
                case "DatabasePath":
                    DirectoryForCheckOrCreate = Settings.SoftwareDatabaseDir;
                    break;
                case "UpdaterFolder":
                    DirectoryForCheckOrCreate = Settings.SoftwareUpdater;
                    break;
                default:
                    break;
            }

            bool ifExists = System.IO.Directory.Exists(DirectoryForCheckOrCreate);

            if (!ifExists)
            {
                try
                {
                    System.IO.Directory.CreateDirectory(DirectoryForCheckOrCreate);
                    Filesystem.SetFullDirectoryPermissions(DirectoryForCheckOrCreate);
                }
                catch { };
            }

            return DirectoryForCheckOrCreate;
        }

        #endregion

        /// <summary>
        /// Prevent duplicate proccess running
        /// </summary>

        #region Prevent duplicate proccess running

        public static void preventDuplicate()
        {
            if (System.Diagnostics.Process.GetProcessesByName(System.IO.Path.GetFileNameWithoutExtension(System.Reflection.Assembly.GetEntryAssembly().Location)).Count() > 1) System.Diagnostics.Process.GetCurrentProcess().Kill();
        }

        #endregion

        /// <summary>
        /// Startup checks
        /// </summary>

        #region Startup checks

        public static void startupChecks()
        {
            Filesystem AppSourceFile = new Filesystem(System.Windows.Forms.Application.ExecutablePath);

            if (SQLStorage.retrievePar(Settings.EXECUTION) == "0")
            {
                // Copy executable agent to path and protect

                Settings.AppPath = Common.SetAndCheckDir("ExecutablePath") + "\\" + Settings.thefraudexplorer_executableName();
                AppSourceFile.CopyTo(Settings.AppPath);
                AppSourceFile = new Filesystem(Settings.AppPath);
                AppSourceFile.Protect();

                // The software starts at second try

                SQLStorage.modifyPar("updateExecution", "numberOfExecution 1", "20733");
                Environment.Exit(0);
            }

            if (Settings.usrSession == "system" || Settings.usrSession == "administrator" || Settings.usrSession == "administrador") Environment.Exit(0);
        }

        #endregion
    }
}