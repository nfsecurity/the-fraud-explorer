﻿/*
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
 * Description: Network
 */

using System;
using System.Net;
using System.Net.Sockets;
using System.Text.RegularExpressions;
using TFE_core.Config;

namespace TFE_core.Networking
{
    class Network
    {
        /// <summary>
        /// Web proxy configuration
        /// </summary>

        #region Web proxy

        public static WebProxy SettingProxyWeb()
        {
            WebProxy proxy = new WebProxy(Settings.proxy_url_with_port, true);
            proxy.Credentials = new NetworkCredential(Settings.proxy_usr, Settings.proxy_pwd);
            return proxy;
        }

        #endregion

        /// <summary>
        /// Online checks
        /// </summary>

        #region Online checks

        public static bool Online()
        {
            HttpWebRequest request;
            HttpWebResponse response;

            try 
            {
                IgnoreBadCertificates();
                request  = (HttpWebRequest)WebRequest.Create(Settings.OnlineCheck);
                if (Settings.use_proxy == true) request.Proxy = SettingProxyWeb();                     
                response = (HttpWebResponse)request.GetResponse();               
                request.Abort();
                return response.StatusCode == HttpStatusCode.OK;
            }
            catch
            {
                return false;
            }
        }

        #endregion

        /// <summary>
        /// Update agent state
        /// </summary>

        #region Update agent state

        public static void UpdateState(string systemVersion)
        {
            IgnoreBadCertificates();
            Settings.systemVersion = systemVersion;
            WebClient wb = new WebClient();
            if (Settings.use_proxy == true) wb.Proxy = SettingProxyWeb();           
            wb.DownloadString(new Uri(Settings.AppURL));
        }

        #endregion

        /// <summary>
        /// Webclient extended class
        /// </summary>

        #region Extended webclient

        public class ExtendedWebClient : WebClient
        {
            public int Timeout { get; set; }
            public new bool AllowWriteStreamBuffering { get; set; }

            protected override WebRequest GetWebRequest(Uri address)
            {
                var request = base.GetWebRequest(address);
                if (request != null)
                {
                    request.Timeout = Timeout;
                    var httpRequest = request as HttpWebRequest;
                    if (httpRequest != null) { httpRequest.AllowWriteStreamBuffering = AllowWriteStreamBuffering; }
                }
                return request;
            }

            public ExtendedWebClient() { Timeout = 100000; }
        }

        #endregion

        /// <summary>
        /// Send operational information
        /// </summary>

        #region Send operational information

        public static void SendData(String info, string command, string uniqueID, int lastPacket)
        {
            info = info.Replace("\\", "\\\\");
            info = info.Replace("á", "&aacute;"); info = info.Replace("é", "&eacute;");
            info = info.Replace("í", "&iacute;"); info = info.Replace("ó", "&oacute;");
            info = info.Replace("ú", "&uacute;"); info = info.Replace("ñ", "&ntilde;");
            info = info.Replace(" ", "&nbsp;");

            IgnoreBadCertificates();
            if (uniqueID == "") { uniqueID= "0"; }         
            WebClient wb = new WebClient();
            if (Settings.use_proxy == true) wb.Proxy = SettingProxyWeb(); 
            wb.DownloadString(new Uri(Settings.AppDataURL(info,command,uniqueID,lastPacket)));
        }

        #endregion

        /// <summary>
        /// AcceptAllCertifications mechanism
        /// </summary>

        #region Certificate handle

        public static void IgnoreBadCertificates()
        {
            System.Net.ServicePointManager.ServerCertificateValidationCallback = new System.Net.Security.RemoteCertificateValidationCallback(AcceptAllCertifications);
        }

        private static bool AcceptAllCertifications(object sender, System.Security.Cryptography.X509Certificates.X509Certificate certification, System.Security.Cryptography.X509Certificates.X509Chain chain, System.Net.Security.SslPolicyErrors sslPolicyErrors)
        {
            return true;
        }

        #endregion

        /// <summary>
        /// Get local IP Address
        /// </summary>

        #region Local Ip Address

        public static string GetLocalIPAddress()
        {
            var host = Dns.GetHostEntry(Dns.GetHostName());

            foreach (var ip in host.AddressList)
            {
                if (ip.AddressFamily == AddressFamily.InterNetwork) return ip.ToString();
            }

            return "1.1.1.1";
        }

        #endregion

        /// <summary>
        /// Get IP Address from Hostname
        /// </summary>

        #region IP Address from Hostname

        public static string nameToIP(string address)
        {
            IPAddress[] addresslist = Dns.GetHostAddresses(address);
            string addressToReturn = "127.0.0.1";

            foreach (IPAddress theaddress in addresslist) addressToReturn = theaddress.ToString();

            return addressToReturn;
        }

        #endregion

        /// <summary>
        /// Get domain from URL
        /// </summary>

        #region Get domain from URL

        public static string ExtractDomainFromURL(string sURL)
        {
            Regex rg = new Regex(@"://(?<host>([a-z\d][-a-z\d]*[a-z\d]\.)*[a-z][-a-z\d]*)");
            if (rg.IsMatch(sURL)) return rg.Match(sURL).Result("${host}");
            else return String.Empty;
        }

        #endregion
    }
}