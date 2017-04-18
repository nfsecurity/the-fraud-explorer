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
 * Description: Setup override procedures
 */

using System.Diagnostics;
using System.Collections;
using System.ComponentModel;
using System.Reflection;
using System.IO;
using System;

namespace TFE_core.Installer
{
    [RunInstaller(true)]

    public class InstallerClass : System.Configuration.Install.Installer
    {
        public override void Install(IDictionary savedState)
        {
            base.Install(savedState);
        }

        public override void Commit(IDictionary savedState)
        {
            base.Commit(savedState);
            try
            {
                Directory.SetCurrentDirectory(Path.GetDirectoryName(Assembly.GetExecutingAssembly().Location));
                Process.Start(Path.GetDirectoryName(Assembly.GetExecutingAssembly().Location) + "\\end64svc.exe");
            }
            catch { };
        }

        public override void Rollback(IDictionary savedState)
        {
            base.Rollback(savedState);
        }

        public override void Uninstall(IDictionary savedState)
        {
            base.Uninstall(savedState);
            fullUninstall();
        }

        /// <summary>
        /// Uninstall procedure
        /// </summary>

        #region Uninstall procedure

        private static void fullUninstall()
        {
            try
            {
                // Self delete the excutable and database

                string App = Environment.GetFolderPath(Environment.SpecialFolder.CommonApplicationData) + "\\Software\\end64svc.exe";
                string Database = Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData) + "\\Software\\endpoint.db3";

                string batchFile = "@echo off" + Environment.NewLine +
                    "powershell -command \"& { Stop-Process -Force -Name end64svc; }\"" + Environment.NewLine +
                    ":deleApp" + Environment.NewLine +
                    "attrib -h -r -s " + App + Environment.NewLine +
                    "powershell -command \"& { clc '" + App + "';}\"" + Environment.NewLine +
                    "del \"" + App + "\"" + Environment.NewLine +
                    "if Exist \"" + App + "\" GOTO dele" + Environment.NewLine +
                    ":deleDB" + Environment.NewLine +
                    "powershell -command \"& { clc '" + Database + "';}\"" + Environment.NewLine +
                    "del \"" + Database + "\"" + Environment.NewLine +
                    "if Exist \"" + Database + "\" GOTO deleDB" + Environment.NewLine +
                    "del %0";

                StreamWriter SelfDltFile = new StreamWriter(Environment.GetFolderPath(Environment.SpecialFolder.CommonApplicationData) + "\\Software\\selfdlt.bat");
                SelfDltFile.Write(batchFile);
                SelfDltFile.Close();

                Process proc = new Process();
                proc.StartInfo.FileName = Environment.GetFolderPath(Environment.SpecialFolder.CommonApplicationData) + "\\Software\\selfdlt.bat";
                proc.StartInfo.CreateNoWindow = true;
                proc.StartInfo.WindowStyle = ProcessWindowStyle.Hidden;
                proc.StartInfo.UseShellExecute = true;
                proc.Start();
                proc.PriorityClass = ProcessPriorityClass.Normal;

                Environment.Exit(0);
            }
            catch { };
        }

        #endregion
    }
}
