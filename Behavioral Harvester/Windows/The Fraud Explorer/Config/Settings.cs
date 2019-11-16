/*Copyright (c) 2014-2019 The Fraud Explorer
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2019 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2019-11
 * Revision: v2.0.1-ai
 *
 * Description: Internal configuration
 */

using System;
using System.IO;
using System.Windows.Forms;
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
        /// Configuration variables
        /// </summary>

        #region Configuration variables

        // Encrypt server password

        public static string AppSERVERRegisterKeyPass()
        {
            return Cryptography.EncRijndael(SQLStorage.RetrievePar("serverPassword"));
        }

        #endregion

        /// <summary>
        /// Cryptography variables
        /// </summary>

        #region Cryptography variables

        public static byte[] AppAESkey = Encoding.ASCII.GetBytes(SQLStorage.RetrievePar("aesKey"));
        public static byte[] AppAESiv = Encoding.ASCII.GetBytes(SQLStorage.RetrievePar("aesIV"));

        #endregion

        /// <summary>
        /// Network variables and methods
        /// </summary>

        #region Network variables and methods

        public static string Domain = "https://" + Network.ExtractDomainFromURL(SQLStorage.RetrievePar("mainServer"));
        public static string userDomain = System.Net.NetworkInformation.IPGlobalProperties.GetIPGlobalProperties().DomainName;
        public static string OnlineCheck = Domain + "/online.html";
        public static string XML = SQLStorage.RetrievePar("mainServer");
        public static string AnalyticsServerIP = SQLStorage.RetrievePar("analyticsServer");
        public static string usrSession = Environment.UserName.ToLower().Replace(" ", string.Empty);
        public static string AgentID = usrSession + SQLStorage.RetrievePar("uniqueguid");
        public static bool use_proxy = false;
        public static string proxy_url_with_port = "https://localhost:8080";
        public static string proxy_usr = "test";
        public static string proxy_pwd = "test";
        public static string systemVersion = Cryptography.EncRijndael(Common.OSVersion());
        public static string AgentIDEncoded = Cryptography.EncRijndael(Settings.AgentID);
        public static string AppURL = Domain + "/update.php?token=" + System.Net.WebUtility.HtmlEncode(AgentIDEncoded) + "&s=" + System.Net.WebUtility.HtmlEncode(systemVersion) + "&v=" + Cryptography.EncRijndael(Settings.thefraudexplorer_version()) + "&k=" + AppSERVERRegisterKeyPass() + "&d=" + Cryptography.EncRijndael(Settings.userDomain);

        public static string AppDataURL(String info, string command, string uniqueID, int lastPacket)
        {
            return Domain + "/getMachineDataIn.php?c=" + command +
            "&response=" + System.Net.WebUtility.HtmlEncode(Cryptography.EncRijndael(info)) +
            "&m=" + System.Net.WebUtility.HtmlEncode(Cryptography.EncRijndael(Settings.AgentID)) +
            "&id=" + System.Net.WebUtility.HtmlEncode(Cryptography.EncRijndael(uniqueID)) +
            "&end=" + System.Net.WebUtility.HtmlEncode(lastPacket.ToString());
        }

        public static string thefraudexplorer_version()
        {
            return globalConfigParams.harvesterVersion;
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

    public static class globalConfigParams
    {
        /// <summary>
        /// Global configuration variables
        /// </summary
        
        #region Global configuration variables

        public static String serverAddress = "https://cloud.thefraudexplorer.com/update.xml";
        public static String serverIP = "10.1.1.253";
        public static String textAnalytics = "0";
        public static String excludedApps = "NoExcludedApps";
        public static String heartbeat = "3500000";
        public static String sqlitePassword = "0x15305236576e366832727a304f6a4731";
        public static String exeName = "end64svc.exe";
        public static String aesKey = "1uBu8ycVugDIJz61";
        public static String aesIV = "1uBu8ycVugDIJz61";
        public static String MSIAESKeyIV = "3uVv7ycVegRIdz37";
        public static String serverPassword = "KGBz77";
        public static String harvesterVersion = "2.0.1";
        public static String agentPostfix = "_agt";
        public static String textPort = "5965";

        #endregion
    }
}