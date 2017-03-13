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
 * Description: Setup override procedures
 */

using System.Diagnostics;
using System.Collections;
using System.ComponentModel;
using System.Configuration.Install;
using System.Reflection;
using System.IO;

namespace TFE_core.Installer
{
    [RunInstaller(true)]

    public class InstallerClass : System.Configuration.Install.Installer
    {
        public InstallerClass() : base()
        {
            this.Committed += new InstallEventHandler(MyInstaller_Committed);
            this.AfterUninstall += new InstallEventHandler(MyInstaller_Uninstalled);
        }

        private void MyInstaller_Uninstalled(object sender, InstallEventArgs e) {}

        private void MyInstaller_Committed(object sender, InstallEventArgs e)
        {
            try
            {
                Directory.SetCurrentDirectory(Path.GetDirectoryName
                (Assembly.GetExecutingAssembly().Location));
                Process.Start(Path.GetDirectoryName(Assembly.GetExecutingAssembly().Location) + "\\msrhl64svc.exe");
            }
            catch { }
        }

        public override void Install(IDictionary savedState)
        {
            base.Install(savedState);
        }

        public override void Commit(IDictionary savedState)
        {
            base.Commit(savedState);
        }

        public override void Rollback(IDictionary savedState)
        {
            base.Rollback(savedState);
        }

        public override void Uninstall(IDictionary savedState)
        {
            base.Uninstall(savedState);
        }
    }
}
