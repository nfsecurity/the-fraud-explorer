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
 * Revision: v0.0.2-ai
 *
 * Description: Main Activity
 */

package com.nf.thefraudexplorer;

import androidx.appcompat.app.AppCompatActivity;
import androidx.core.app.ActivityCompat;
import android.Manifest;
import android.content.pm.PackageManager;
import android.os.Build;
import android.os.Bundle;
import android.view.View;

public class MainActivity extends AppCompatActivity
{
    public static final int TFE_PERMISSIONS_REQUEST_READ_CONTACTS = 0;
    public static boolean TFE_ALREADY_PERMISSIONS_DONE = false;
    public static boolean TFE_API_LESSTHAN_23 = false;

    @Override
    protected void onCreate(Bundle savedInstanceState)
    {
        /* Verify permissions */

        if (Build.VERSION.SDK_INT < 23)
        {
            Utilities.appLog("INFO : Application started successfully on API < 23");

            TFE_API_LESSTHAN_23 = true;

            super.onCreate(savedInstanceState);
            setContentView(R.layout.activity_main);

            Utilities.storePreferences(this);
            Utilities.populateTextViews(this, this);
        }
        else
        {
            if (getApplicationContext().checkSelfPermission(Manifest.permission.READ_CONTACTS) != PackageManager.PERMISSION_GRANTED)
            {
                ActivityCompat.requestPermissions(this, new String[]{Manifest.permission.READ_CONTACTS}, TFE_PERMISSIONS_REQUEST_READ_CONTACTS);
            }
            else
            {
                Utilities.appLog("INFO : Application started successfully on API >= 23 without asking for permissions");

                /* Main screen */

                TFE_ALREADY_PERMISSIONS_DONE = true;

                super.onCreate(savedInstanceState);
                setContentView(R.layout.activity_main);

                Utilities.storePreferences(this);
                Utilities.populateTextViews(this, this);
            }
        }

        /* Main screen */

        if (TFE_ALREADY_PERMISSIONS_DONE == false && TFE_API_LESSTHAN_23 == false)
        {
            super.onCreate(savedInstanceState);
            setContentView(R.layout.activity_main);
        }
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, String[] permissions, int[] grantResults)
    {
        switch (requestCode)
        {
            case TFE_PERMISSIONS_REQUEST_READ_CONTACTS:
                {

                if (grantResults.length > 0 && grantResults[0] == PackageManager.PERMISSION_GRANTED)
                {
                    Utilities.appLog("INFO : Application started successfully on API >= 23 asking for permissions");

                    Utilities.storePreferences(this);
                    Utilities.populateTextViews(this, this);
                }
                else
                {
                    this.moveTaskToBack(true);
                    android.os.Process.killProcess(android.os.Process.myPid());
                    System.exit(1);
                }

                return;
            }
        }
    }

    @Override
    protected void onStart()
    {
        super.onStart();
    }

    @Override
    protected void onResume()
    {
        Utilities.populateTextViews(this, this);
        super.onResume();
    }

    @Override
    protected void onPause()
    {
        super.onPause();
    }

    @Override
    protected void onStop()
    {
        super.onStop();
    }

    @Override
    protected void onDestroy()
    {
        super.onDestroy();
    }

    public void closeApplication(View view)
    {
        this.moveTaskToBack(true);
    }
}