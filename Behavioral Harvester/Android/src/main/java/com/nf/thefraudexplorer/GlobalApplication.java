/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2019 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2019-06
 * Revision: v0.0.1-ai
 *
 * Description: Global Context
 */

package com.nf.thefraudexplorer;

import android.app.Application;
import android.content.Context;

public class GlobalApplication extends Application
{
    private static Context appContext;

    @Override
    public void onCreate()
    {
        super.onCreate();
        appContext = getApplicationContext();
    }

    public static Context getAppContext()
    {
        return appContext;
    }
}
