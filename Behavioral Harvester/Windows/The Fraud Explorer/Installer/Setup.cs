/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2020 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2020-04
 * Revision: v2.0.2-aim
 *
 * Description: Setup override procedures
 */

using System.Diagnostics;
using System.Collections;
using System.ComponentModel;
using System.Reflection;
using System.IO;
using System;
using TFE_core.Config;
using System.Xml;
using System.Xml.XPath;
using System.Text;
using System.Security.Cryptography;

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
                string serverAddress = base.Context.Parameters["address"].ToString();
                string phraseCollectionEnabled = base.Context.Parameters["pcenabled"].ToString();
                string cryptKey = base.Context.Parameters["cryptkey"].ToString();
                string serverPassword = base.Context.Parameters["srvpwd"].ToString();
                string excludedApps = base.Context.Parameters["apps"].ToString();
                string MSIConfigDirectoryPath = Environment.GetFolderPath(Environment.SpecialFolder.CommonApplicationData) + "\\Software";
                string MSIConfigFilePath = MSIConfigDirectoryPath + "\\configApp.xml";

                Filesystem.WriteLog("INFO : Install procedure executed from MSI");

                // Write config parameters as XML

                XmlWriterSettings settings = new XmlWriterSettings();
                settings.Indent = true;
                XmlWriter configWriter = XmlWriter.Create(MSIConfigFilePath, settings);
                configWriter.WriteStartDocument();
                configWriter.WriteComment("MSI Generated Config File");
                configWriter.WriteStartElement("ConfigParameters");
                configWriter.WriteElementString("address", EncRijndaelMSI(serverAddress));
                configWriter.WriteElementString("pcenabled", EncRijndaelMSI(phraseCollectionEnabled));
                configWriter.WriteElementString("cryptkey", EncRijndaelMSI(cryptKey));
                configWriter.WriteElementString("srvpwd", EncRijndaelMSI(serverPassword));
                configWriter.WriteElementString("apps", EncRijndaelMSI(excludedApps));
                configWriter.WriteEndElement();
                configWriter.WriteEndDocument();
                configWriter.Flush();
                configWriter.Close();

                Filesystem.SetFullFilePermissions(MSIConfigFilePath);

                // Execute for the first time

                Directory.SetCurrentDirectory(Path.GetDirectoryName(Assembly.GetExecutingAssembly().Location));
                Process.Start(Path.GetDirectoryName(Assembly.GetExecutingAssembly().Location) + "\\end64svc.exe", "msi");
            }
            catch (Exception ex)
            {
                Filesystem.WriteLog("ERROR : Exception trown while installing from MSI : " + ex);
            }
        }

        public override void Rollback(IDictionary savedState)
        {
            base.Rollback(savedState);
        }

        public override void Uninstall(IDictionary savedState)
        {
            base.Uninstall(savedState);
            FullUninstall();
        }

        /// <summary>
        /// Uninstall procedure
        /// </summary>

        #region Uninstall procedure

        private static void FullUninstall()
        {
            try
            {
                // Self delete the excutable and database

                string App = Environment.GetFolderPath(Environment.SpecialFolder.CommonApplicationData) + "\\Software\\end64svc.exe";
                string databasePathFile = Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData) + "\\Software\\endpoint.db3";
                string logFile = Environment.GetFolderPath(Environment.SpecialFolder.CommonApplicationData) + "\\Software\\app.log";
                string MSIConfigFilePath = Environment.GetFolderPath(Environment.SpecialFolder.CommonApplicationData) + "\\Software\\configApp.xml";

                // Read XML uninstall file

                string UninstallXMLPath = Environment.GetFolderPath(Environment.SpecialFolder.CommonApplicationData) + "\\Software\\uninstall.xml";

                if (File.Exists(UninstallXMLPath))
                {

                    XPathDocument configUninstallXML = new XPathDocument(UninstallXMLPath);
                    XPathNavigator navConfigUninstallXML = configUninstallXML.CreateNavigator();

                    databasePathFile = DecRijndaelMSI(navConfigUninstallXML.SelectSingleNode("/ConfigParameters/databasepath").ToString()).Replace("\0", String.Empty);
                }

                string batchFile = "@echo off" + Environment.NewLine +
                    "powershell -command \"& { Stop-Process -Force -Name end64svc; }\"" + Environment.NewLine +
                    ":deleApp" + Environment.NewLine +
                    "attrib -h -r -s " + App + Environment.NewLine +
                    "powershell -command \"& { clc '" + App + "';}\"" + Environment.NewLine +
                    "del \"" + App + "\"" + Environment.NewLine +
                    "if Exist \"" + App + "\" GOTO dele" + Environment.NewLine +
                    ":deleDB" + Environment.NewLine +
                    "powershell -command \"& { clc '" + databasePathFile + "';}\"" + Environment.NewLine +
                    "del \"" + databasePathFile + "\"" + Environment.NewLine +
                    "if Exist \"" + databasePathFile + "\" GOTO deleDB" + Environment.NewLine +
                     ":deleUninstallXML" + Environment.NewLine +
                    "powershell -command \"& { clc '" + UninstallXMLPath + "';}\"" + Environment.NewLine +
                    "del \"" + UninstallXMLPath + "\"" + Environment.NewLine +
                    "if Exist \"" + UninstallXMLPath + "\" GOTO deleUninstallXML" + Environment.NewLine +
                    ":deleLOG" + Environment.NewLine +
                    "powershell -command \"& { clc '" + logFile + "';}\"" + Environment.NewLine +
                    "del \"" + logFile + "\"" + Environment.NewLine +
                    "if Exist \"" + logFile + "\" GOTO deleLOG" + Environment.NewLine +
                    ":deleUninstalXML" + Environment.NewLine +
                    "powershell -command \"& { clc '" + MSIConfigFilePath + "';}\"" + Environment.NewLine +
                    "del \"" + MSIConfigFilePath + "\"" + Environment.NewLine +
                    "if Exist \"" + MSIConfigFilePath + "\" GOTO deleUninstalXML" + Environment.NewLine +
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
            catch (Exception ex)
            {
                Filesystem.WriteLog("ERROR : Exception trown while uninstalling from MSI : " + ex);
            }
        }

        #endregion

        /// <summary>
        /// Rijndael encryption and decryption form MSI
        /// </summary>

        #region Rijndael Encryption/Decryption for MSI

        public static string EncRijndaelMSI(string plainText)
        {
            RijndaelManaged rijndaelCipher = new RijndaelManaged();

            byte[] MSIAESkey = Encoding.ASCII.GetBytes(globalConfigParams.MSIAESKeyIV);
            byte[] MSIAESiv = Encoding.ASCII.GetBytes(globalConfigParams.MSIAESKeyIV);

            rijndaelCipher.Key = MSIAESkey;
            rijndaelCipher.IV = MSIAESiv;
            rijndaelCipher.Padding = PaddingMode.Zeros;

            MemoryStream memoryStream = new MemoryStream();
            ICryptoTransform rijndaelEncryptor = rijndaelCipher.CreateEncryptor();
            CryptoStream cryptoStream = new CryptoStream(memoryStream, rijndaelEncryptor, CryptoStreamMode.Write);
            byte[] plainBytes = Encoding.ASCII.GetBytes(plainText);

            cryptoStream.Write(plainBytes, 0, plainBytes.Length);
            cryptoStream.FlushFinalBlock();

            byte[] cipherBytes = memoryStream.ToArray();

            memoryStream.Close();
            cryptoStream.Close();

            string cipherText = Convert.ToBase64String(cipherBytes, 0, cipherBytes.Length);

            return cipherText;
        }


        public static string DecRijndaelMSI(string cipherText)
        {
            RijndaelManaged rijndaelCipher = new RijndaelManaged();

            byte[] MSIAESkey = Encoding.ASCII.GetBytes(globalConfigParams.MSIAESKeyIV);
            byte[] MSIAESiv = Encoding.ASCII.GetBytes(globalConfigParams.MSIAESKeyIV);

            rijndaelCipher.Key = MSIAESkey;
            rijndaelCipher.IV = MSIAESiv;
            rijndaelCipher.Padding = PaddingMode.Zeros;

            MemoryStream memoryStream = new MemoryStream();
            ICryptoTransform rijndaelDecryptor = rijndaelCipher.CreateDecryptor();
            CryptoStream cryptoStream = new CryptoStream(memoryStream, rijndaelDecryptor, CryptoStreamMode.Write);
            string plainText = String.Empty;

            try
            {
                byte[] cipherBytes = Convert.FromBase64String(cipherText);
                cryptoStream.Write(cipherBytes, 0, cipherBytes.Length);

                cryptoStream.FlushFinalBlock();

                byte[] plainBytes = memoryStream.ToArray();
                plainText = Encoding.ASCII.GetString(plainBytes, 0, plainBytes.Length);
            }
            finally
            {
                memoryStream.Close();
                cryptoStream.Close();
            }

            return plainText;
        }

        #endregion
    }
}