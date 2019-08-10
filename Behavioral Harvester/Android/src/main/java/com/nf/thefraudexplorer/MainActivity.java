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
 * Description: Main Activity
 */

package com.nf.thefraudexplorer;

import androidx.appcompat.app.AppCompatActivity;
import androidx.core.app.ActivityCompat;
import android.Manifest;
import android.content.pm.PackageManager;
import android.os.Build;
import android.os.Bundle;
import android.util.Log;
import android.view.View;

public class MainActivity extends AppCompatActivity
{
    public static final int TFE_PERMISSIONS_REQUEST_READ_CONTACTS = 0;

    @Override
    protected void onCreate(Bundle savedInstanceState)
    {
        Utilities.appLog("INFO : Application started successfully");

        /* Verify permissions */

        if (Build.VERSION.SDK_INT < 23)
        {
            Utilities.appLog("INFO : Detected Android API Level minor than 23");

            Utilities.storePreferences(this);
        }
        else
        {
            if (getApplicationContext().checkSelfPermission(Manifest.permission.READ_CONTACTS) != PackageManager.PERMISSION_GRANTED)
            {
                ActivityCompat.requestPermissions(this, new String[]{Manifest.permission.READ_CONTACTS}, TFE_PERMISSIONS_REQUEST_READ_CONTACTS);
            }
        }

        /* Populate main screen properties */

        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        Utilities.storePreferences(this);
        Utilities.populateTextViews(this, this);
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
                    Utilities.storePreferences(this);
                    Utilities.populateTextViews(this, this);
                }
                else
                {
                    this.moveTaskToBack(true);
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