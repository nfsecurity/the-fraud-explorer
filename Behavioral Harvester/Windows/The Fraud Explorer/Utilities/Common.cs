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
 * Description: Operating system
 */

using System;
using System.IO;
using System.Linq;
using System.Xml;
using TFE_core.Installer;

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
        /// Prevent duplicate proccess running
        /// </summary>

        #region Prevent duplicate proccess running

        public static void PreventDuplicate()
        {
            if (System.Diagnostics.Process.GetProcessesByName(System.IO.Path.GetFileNameWithoutExtension(System.Reflection.Assembly.GetEntryAssembly().Location)).Count() > 1)
            {
                Filesystem.WriteLog("ERROR : Detected duplicated process running, killed execution");
                System.Diagnostics.Process.GetCurrentProcess().Kill();
            }
        }

        #endregion

        /// <summary>
        /// Startup checks
        /// </summary>

        #region Startup checks

        public static void StartupChecks(string entryPoint)
        {
            try
            {
                Filesystem AppSourceFile = new Filesystem(System.Windows.Forms.Application.ExecutablePath);
                string userSession = Environment.UserName.ToLower().Replace(" ", string.Empty);

                if (entryPoint == "msi")
                {
                    // Copy executable endpoint to path and protect

                    Filesystem.WriteLog("INFO : Startup check, MSI as argument in app execution");

                    string fromMSIAppPath = Environment.GetFolderPath(Environment.SpecialFolder.CommonApplicationData) + "\\Software\\" + globalConfigParams.exeName;

                    AppSourceFile.CopyTo(fromMSIAppPath);
                    AppSourceFile = new Filesystem(fromMSIAppPath);
                    AppSourceFile.Protect();

                    Filesystem.WriteLog("INFO : Exiting because it's the first execution");

                    Environment.Exit(0);
                }
                else if (entryPoint == "smoothrun")
                {
                    Filesystem.WriteLog("INFO : Startup check finished, running in smooth");

                    // Store in XML the user database path (for uninstall purposes)

                    string UninstallXMLPath = Environment.GetFolderPath(Environment.SpecialFolder.CommonApplicationData) + "\\Software\\uninstall.xml";

                    if (File.Exists(UninstallXMLPath)) File.Delete(UninstallXMLPath);
                    
                    XmlWriterSettings settings = new XmlWriterSettings();
                    settings.Indent = true;
                    XmlWriter configWriter = XmlWriter.Create(UninstallXMLPath, settings);
                    configWriter.WriteStartDocument();
                    configWriter.WriteComment("Config file for uninstall purposes");
                    configWriter.WriteStartElement("ConfigParameters");
                    configWriter.WriteElementString("databasepath", InstallerClass.EncRijndaelMSI(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData) + "\\Software\\endpoint.db3"));
                    configWriter.WriteEndElement();
                    configWriter.WriteEndDocument();
                    configWriter.Flush();
                    configWriter.Close();

                    Filesystem.SetFullFilePermissions(UninstallXMLPath);    
                }

                if (userSession == "system" || userSession == "administrator" || userSession == "administrador") Environment.Exit(0);
            }
            catch (Exception ex)
            {
                Filesystem.WriteLog("ERROR : Exception trown in Startup Checks : " + ex);
            }
        }

        #endregion
    }
}