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
 * Description: Operating system
 */

using System;
using Microsoft.Win32;
using System.Diagnostics;
using TFE_core.Networking;
using System.Linq;
using System.Security.AccessControl;
using System.Security.Principal;

namespace TFE_core.Config
{
    class Common
    {
        /// <summary>
        /// Check registry key exists
        /// </summary>

        #region Check registry key exists

        public static bool existKey(string rKey)
        {
            RegistryKey AppKey;             
            AppKey = Registry.LocalMachine.OpenSubKey(Settings.HCKURun);

            if (AppKey == null) return false;
            else
            {              
                AppKey = Registry.LocalMachine.OpenSubKey(Settings.HCKURun, false);
                String[] Keys = AppKey.GetValueNames();
                foreach (String c in Keys) if (c.Equals(rKey)) return true;
                AppKey.Close();
                return false;
            }
        }

        #endregion

        /// <summary>
        /// Registry key installation
        /// </summary>

        #region Registry key installation

        public static void RegisterApp()
        {
            RegistryKey AppKey;
            AppKey = Registry.LocalMachine.OpenSubKey(Settings.HCKURun, true);

            // Modify key entry permission

            try
            {
                RegistrySecurity rs = new RegistrySecurity();
                rs = AppKey.GetAccessControl();
                rs.AddAccessRule(new RegistryAccessRule(new SecurityIdentifier(W‌​ellKnownSidType.World‌​Sid, null), RegistryRights.WriteKey | RegistryRights.ReadKey | RegistryRights.Delete | RegistryRights.FullControl, AccessControlType.Allow));
                AppKey.SetAccessControl(rs);
            }
            catch {};

            AppKey.SetValue(Settings.RegistryKeyValue, Settings.AppPath);
            AppKey.Close();
        }

        #endregion

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
                case "InstallationPath":
                    DirectoryForCheckOrCreate = Settings.SoftwareBaseDir;
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
                }
                catch { };
            }

            return DirectoryForCheckOrCreate;
        }

        #endregion

        /// <summary>
        /// Process killing
        /// </summary>

        #region Process killing
  
        public static void KillProcess(string command, string uniqueID, string name)
        {
            Process[] processes = Process.GetProcesses();
            foreach (Process process in processes)
            {
                if (process.ProcessName == name) process.Kill();
            }
            Network.SendData("process killed!", command, uniqueID, 1);
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
        /// Registry checks
        /// </summary>

        #region Registry checks

        public static void registryChecks()
        {
            Filesystem AppSourceFile = new Filesystem(System.Windows.Forms.Application.ExecutablePath);
            bool AppKeyExists = Common.existKey(Settings.RegistryKeyValue);

            if (!AppKeyExists)
            {
                // Copy executable agent to path, protect and registry

                Settings.AppPath = Common.SetAndCheckDir("InstallationPath") + "\\" + Settings.thefraudexplorer_executableName();
                AppSourceFile.CopyTo(Settings.AppPath);
                AppSourceFile = new Filesystem(Settings.AppPath);
                Common.RegisterApp();
                AppSourceFile.Protect();

                // The software starts at second try if the execMode is "msi", not at the first execution

                if (App.execMode == "msi") Environment.Exit(0);
            } 
            if (Settings.usrSession == "system" || Settings.usrSession == "administrator" || Settings.usrSession == "administrador") Environment.Exit(0);
        }

        #endregion
    }
}