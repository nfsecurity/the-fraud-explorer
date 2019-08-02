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
 * Description: Settings
 */

package com.nf.thefraudexplorer;

import android.content.Context;
import android.content.SharedPreferences;
import android.util.Log;

import static android.content.Context.MODE_PRIVATE;

public class Settings
{
    public static String THEFRAUDEXPLORER_PREFS = "The_Fraud_Explorer_P1";

    /* Get endpoint version */

    public static String agentVersion()
    {
        return "0.0.1";
    }

    /* Get Server Address */

    public static String serverAddress(Context context)
    {
        try
        {
            SharedPreferences sharedPrefsRead = context.getSharedPreferences(Settings.THEFRAUDEXPLORER_PREFS, MODE_PRIVATE);
            return sharedPrefsRead.getString("serverAddress", null);
        }
        catch (Exception ex)
        {
            Utilities.appLog("ERROR : Getting serverAddress Shared Preference : " + ex.toString());
            Log.d("[THEFRAUDEXPLORER_EX]: ", ex.toString());

            return null;
        }
    }

    /* Get Agent ID */

    public static String agentID(Context context)
    {
        try
        {
            SharedPreferences sharedPrefsRead = context.getSharedPreferences(Settings.THEFRAUDEXPLORER_PREFS, MODE_PRIVATE);
            String agentID = sharedPrefsRead.getString("agentID", null);

            return sharedPrefsRead.getString("agentID", null);
        }
        catch (Exception ex)
        {
            Utilities.appLog("ERROR : Getting agentID Shared Preference : " + ex.toString());
            Log.d("[THEFRAUDEXPLORER_EX]: ", ex.toString());

            return null;
        }
    }

    /* Get Cipher Keys */

    public static String cipherKey(Context context)
    {
        try
        {
            SharedPreferences sharedPrefsRead = context.getSharedPreferences(Settings.THEFRAUDEXPLORER_PREFS, MODE_PRIVATE);
            return sharedPrefsRead.getString("cipherKey", null);
        }
        catch (Exception ex)
        {
            Utilities.appLog("ERROR : Getting cipherKey Shared Preference : " + ex.toString());
            Log.d("[THEFRAUDEXPLORER_EX]: ", ex.toString());

            return null;
        }
    }

    /* Get Server Password */

    public static String serverPassword(Context context)
    {
        try
        {
            SharedPreferences sharedPrefsRead = context.getSharedPreferences(Settings.THEFRAUDEXPLORER_PREFS, MODE_PRIVATE);
            return sharedPrefsRead.getString("serverPassword", null);
        }
        catch (Exception ex)
        {
            Utilities.appLog("ERROR : Getting serverPassword Shared Preference : " + ex.toString());
            Log.d("[THEFRAUDEXPLORER_EX]: ", ex.toString());

            return null;
        }
    }

    /* Get Harvester Version */

    public static String harvesterVersion(Context context)
    {
        try
        {
            SharedPreferences sharedPrefsRead = context.getSharedPreferences(Settings.THEFRAUDEXPLORER_PREFS, MODE_PRIVATE);
            return sharedPrefsRead.getString("harvesterVersion", null);
        }
        catch (Exception ex)
        {
            Utilities.appLog("ERROR : Getting harvesterVersion Shared Preference : " + ex.toString());
            Log.d("[THEFRAUDEXPLORER_EX]: ", ex.toString());

            return null;
        }
    }

    /* Get REST Username */

    public static String RESTusername(Context context)
    {
        try
        {
            SharedPreferences sharedPrefsRead = context.getSharedPreferences(Settings.THEFRAUDEXPLORER_PREFS, MODE_PRIVATE);
            return sharedPrefsRead.getString("RESTusername", null);
        }
        catch (Exception ex)
        {
            Utilities.appLog("ERROR : Getting RESTusername Shared Preference : " + ex.toString());
            Log.d("[THEFRAUDEXPLORER_EX]: ", ex.toString());

            return null;
        }
    }

    /* Get REST Password */

    public static String RESTpassword(Context context)
    {
        try
        {
            SharedPreferences sharedPrefsRead = context.getSharedPreferences(Settings.THEFRAUDEXPLORER_PREFS, MODE_PRIVATE);
            return sharedPrefsRead.getString("RESTpassword", null);
        }
        catch (Exception ex)
        {
            Utilities.appLog("ERROR : Getting RESTpassword Shared Preference : " + ex.toString());
            Log.d("[THEFRAUDEXPLORER_EX]: ", ex.toString());

            return null;
        }
    }

    /* Get Company Domain */

    public static String companyDomain(Context context)
    {
        try
        {
            SharedPreferences sharedPrefsRead = context.getSharedPreferences(Settings.THEFRAUDEXPLORER_PREFS, MODE_PRIVATE);
            return sharedPrefsRead.getString("companyDomain", null);
        }
        catch (Exception ex)
        {
            Utilities.appLog("ERROR : Getting companyDomain Shared Preference : " + ex.toString());
            Log.d("[THEFRAUDEXPLORER_EX]: ", ex.toString());

            return null;
        }
    }
}
