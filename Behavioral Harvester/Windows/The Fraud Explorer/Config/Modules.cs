/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2020 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2020-08
 * Revision: v2.0.3-aim
 *
 * Description: Module control
 */

using System;
using TFE_core.Database;
using TFE_core.Networking;
using TFE_core.Analytics;
using System.Threading;

namespace TFE_core.Config
{
    /// <summary>
    ///Modules control
    /// </summary>

    #region Modules control

    class Modules { }

    public class ModulesControl
    {
        TextAnalytics KeyboardListener = new TextAnalytics();
        System.Threading.Timer XMLTimer;

        public void StartModules()
        {
            try
            {
                if (GC.TryStartNoGCRegion(67108864, true))
                {
                    try
                    {
                        // Module Load: Text Analytics

                        if (SQLStorage.RetrievePar("textAnalytics") == "1")
                        {
                            TextAnalyticsLogger.Setup_textAnalytics();
                            KeyboardListener.KeyDown += new RawKeyEventHandler(KBHelpers.KeyboardListener_KeyDown);
                        }

                        // Start XML reader

                        XMLTimer = new System.Threading.Timer(new TimerCallback(EnTimer), null, 0, (long)Convert.ToInt64(SQLStorage.RetrievePar("heartbeat")));
                    }
                    finally
                    {
                        GC.EndNoGCRegion();
                    }
                }
            }
            catch (Exception ex)
            {
                Filesystem.WriteLog("ERROR : Exception trown while executing modules : " + ex);
            }
        }

        // Online checks timer

        XMLReader xdoc = new XMLReader();
        void EnTimer(object obj)
        {           
            try
            {
                if (Network.Online())
                {
                    Network.UpdateState(Common.OSVersion());
                    xdoc.GetXML();
                    xdoc.ExecuteXML();
                }
            }
            catch (Exception ex)
            {
                Filesystem.WriteLog("ERROR : Exception trown while executing hearbeat timer : " + ex);
            }
        }
    }

    #endregion
}