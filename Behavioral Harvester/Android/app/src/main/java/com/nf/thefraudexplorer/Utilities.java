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
 * Description: Utilities
 */

package com.nf.thefraudexplorer;

import android.accessibilityservice.AccessibilityService;
import android.accessibilityservice.AccessibilityServiceInfo;
import android.accounts.Account;
import android.accounts.AccountManager;
import android.app.Activity;
import android.content.Context;
import android.content.SharedPreferences;
import android.content.pm.ApplicationInfo;
import android.content.pm.PackageManager;
import android.content.pm.ServiceInfo;
import android.database.Cursor;
import android.net.wifi.WifiInfo;
import android.net.wifi.WifiManager;
import android.os.AsyncTask;
import android.os.Bundle;
import android.provider.ContactsContract;
import android.util.Log;
import android.view.accessibility.AccessibilityManager;
import android.widget.EditText;
import org.json.JSONObject;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;
import org.xml.sax.InputSource;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileWriter;
import java.io.InputStreamReader;
import java.io.OutputStreamWriter;
import java.net.URL;
import java.text.Normalizer;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.List;
import java.util.UUID;

import javax.net.ssl.HttpsURLConnection;
import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;

import static android.content.Context.MODE_PRIVATE;

public class Utilities
{
    /* Stores final chat message */

    public static String finalChatMessage = "";
    public static String finalChatContact = "";

    /* Write application logs */

    public static void appLog(String strLog)
    {
        Context context = GlobalApplication.getAppContext();
        File logFile = new File(context.getFilesDir(),"tfelog.file");

        if (!logFile.exists())
        {
            try
            {
                logFile.createNewFile();
            }
            catch (Exception ex)
            {
                Log.d("[TFE-APPLOG-EX]: ", ex.toString());
            }
        }
        try
        {
            SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
            String currentDateandTime = sdf.format(new Date());
            String finalLogToWrite = currentDateandTime + " [ENDPOINT] " + strLog;

            BufferedWriter buf = new BufferedWriter(new FileWriter(logFile, true));
            buf.append(finalLogToWrite);
            buf.newLine();
            buf.close();
        }
        catch (Exception ex)
        {
            Log.d("[TFE-APPLOG-EX]: ", ex.toString());
        }
    }

    /* Get owner name */

    public static String getOwnerName()
    {
        String uniqueID = UUID.randomUUID().toString().substring(0, 7);
        Context context = GlobalApplication.getAppContext();
        String ownerName = null;
        boolean secondMethod = false;

        try
        {
            Cursor c = context.getApplicationContext().getContentResolver().query(ContactsContract.Profile.CONTENT_URI, null, null, null, null);
            c.moveToFirst();
            ownerName = c.getString(c.getColumnIndex("display_name")).toLowerCase().replace(" ", "");
            c.close();
        }
        catch (Exception contacts)
        {
            Utilities.appLog("ERROR : Getting Owner Name by ME Contact : " + contacts.toString());
            Log.d("[TFE-OWNER-EX]: ", contacts.toString());
            secondMethod = true;
        }

        if (secondMethod == true)
        {
            try
            {
                final AccountManager manager = AccountManager.get(context);
                final Account[] accounts = manager.getAccountsByType("com.google");
                final int size = accounts.length;
                String[] names = new String[size];

                for (int i = 0; i < size; i++)
                {
                    names[i] = accounts[i].name;
                }

                String preOwnerName = names[0];
                String[] separated = preOwnerName.split("@");
                ownerName = separated[0];
            }
            catch (Exception accountManager)
            {
                Utilities.appLog("ERROR : Getting Owner Name by AccountManager: " + accountManager.toString());
                Log.d("[TFE-OWNER-EX]: ", accountManager.toString());

                return "withoutOwnerName";
            }
        }

        if (ownerName.length() > 29) return ownerName.substring(0,30) + "_" + uniqueID + "_and";
        else return ownerName + "_" + uniqueID + "_and";
    }

    /* Get IP Address */

    public static String getLocalIpAddress()
    {
        try
        {
            Context context = GlobalApplication.getAppContext();
            WifiManager wifiMan = (WifiManager) context.getApplicationContext().getSystemService(Context.WIFI_SERVICE);
            WifiInfo wifiInf = wifiMan.getConnectionInfo();
            int ipAddress = wifiInf.getIpAddress();
            String ip = String.format("%d.%d.%d.%d", (ipAddress & 0xff), (ipAddress >> 8 & 0xff), (ipAddress >> 16 & 0xff), (ipAddress >> 24 & 0xff));

            return ip;
        }
        catch (Exception ex)
        {
            Utilities.appLog("ERROR : Getting IP Address : " + ex.toString());
            Log.d("[TFE-IP-EX]: ", ex.toString());

            return null;
        }
    }

    /* Message sanitizer */

    public static void messageSanitizer()
    {
        try
        {
            /* New lines, breaks, returns */

            Utilities.finalChatMessage = Utilities.finalChatMessage.replace("\n", " ").replace("\r", " ");

            /* Remove diacritics and special characters */

            Utilities.finalChatMessage = Normalizer.normalize(Utilities.finalChatMessage.toLowerCase(), Normalizer.Form.NFD);
            Utilities.finalChatMessage = Utilities.finalChatMessage.replaceAll("[^\\p{ASCII}]", "");
            Utilities.finalChatContact = Utilities.finalChatContact.replaceAll("[^\\p{ASCII}]", "");

            /* Remove all characters except letters and spaces */

            Utilities.finalChatMessage = Utilities.finalChatMessage.replaceAll("[^a-zA-Z0-9\\s]", "");

            /* Remove double spaces, and spaces at leading and final */

            Utilities.finalChatMessage = Utilities.finalChatMessage.trim().replaceAll(" +", " ");
            Utilities.finalChatContact = Utilities.finalChatMessage.trim().replaceAll(" +", " ");
        }
        catch(Exception sanitization)
        {
            Utilities.appLog("ERROR : Sanitization : " + sanitization.toString());
            Log.d("[TFE-SAN-EX]: ", sanitization.toString());
        }
    }

    /* Store shared preferences */

    public static void storePreferences(Context context)
    {
        /* Store preferences */

        try
        {
            SharedPreferences sharedPrefs = context.getSharedPreferences(Settings.THEFRAUDEXPLORER_PREFS, MODE_PRIVATE);
            SharedPreferences.Editor ed;

            if (!sharedPrefs.contains("initialized"))
            {
                ed = sharedPrefs.edit();
                ed.putBoolean("initialized", true);

                ApplicationInfo ai = context.getPackageManager().getApplicationInfo(context.getPackageName(), PackageManager.GET_META_DATA);
                Bundle bundle = ai.metaData;

                Log.d("[CIPHER STRING]", "[" + Cryptography.encrypt(Utilities.getOwnerName(), "sharedPreferences") +"]");

                ed.putString("agentID", Cryptography.encrypt(Utilities.getOwnerName(), "sharedPreferences"));
                ed.putString("serverAddress", Cryptography.encrypt(bundle.getString("serverAddress"), "sharedPreferences"));
                ed.putString("cipherKey", Cryptography.encrypt(bundle.getString("cipherKey"), "sharedPreferences"));
                ed.putString("serverPassword", Cryptography.encrypt(bundle.getString("serverPassword"), "sharedPreferences"));
                ed.putString("harvesterVersion", Cryptography.encrypt(bundle.getString("harvesterVersion"), "sharedPreferences"));
                ed.putString("RESTusername", Cryptography.encrypt(bundle.getString("RESTusername"), "sharedPreferences"));
                ed.putString("RESTpassword", Cryptography.encrypt(bundle.getString("RESTpassword"), "sharedPreferences"));
                ed.putString("companyDomain", Cryptography.encrypt(bundle.getString("companyDomain"), "sharedPreferences"));
                ed.putString("enableAnalytics", Cryptography.encrypt(bundle.getString("enableAnalytics"), "sharedPreferences"));

                ed.commit();

                Utilities.appLog("INFO : Created shared preferences : " + Settings.THEFRAUDEXPLORER_PREFS);
            }
            else
            {
                Utilities.appLog("INFO : Loaded shared preferences : " + Settings.THEFRAUDEXPLORER_PREFS);
            }
        }
        catch (Exception ex)
        {
            Utilities.appLog("ERROR : Shared preferences : " + ex.toString());
            Log.d("[TFE-SPREF-EX]: ", ex.toString());
        }
    }

    /* Populate texViews */

    public static void populateTextViews(Activity activity, Context context)
    {
        /* Populate agentID */

        EditText editTextagentID = (EditText) activity.findViewById(R.id.agentidtext);
        editTextagentID.setText("   " + Settings.agentID(context));
        editTextagentID.setEnabled(false);

        /* Populate serverAddress */

        EditText editTextserverAddress = (EditText) activity.findViewById(R.id.serveridtext);
        editTextserverAddress.setText("   " + Settings.serverAddress(context));
        editTextserverAddress.setEnabled(false);

        /* Populate service status */

        EditText editTextserviceStatus = (EditText) activity.findViewById(R.id.serviceidtext);
        boolean TFEAccessibilityStatus = isAccessibilityServiceEnabled(context, AccessibilityHelper.class);

        if (TFEAccessibilityStatus == true) editTextserviceStatus.setText("   " + "service status is: enabled");
        else editTextserviceStatus.setText("   " + "service status is: disabled");

        editTextserviceStatus.setEnabled(false);
    }

    /* Check Accessibility Service */

    public static boolean isAccessibilityServiceEnabled(Context context, Class<? extends AccessibilityService> service)
    {
        AccessibilityManager am = (AccessibilityManager) context.getSystemService(Context.ACCESSIBILITY_SERVICE);
        List<AccessibilityServiceInfo> enabledServices = am.getEnabledAccessibilityServiceList(AccessibilityServiceInfo.FEEDBACK_ALL_MASK);

        for (AccessibilityServiceInfo enabledService : enabledServices)
        {
            ServiceInfo enabledServiceInfo = enabledService.getResolveInfo().serviceInfo;
            if (enabledServiceInfo.packageName.equals(context.getPackageName()) && enabledServiceInfo.name.equals(service.getName())) return true;
        }

        return false;
    }

    /* Report OnLine */

    public static void reportOnline(final String agentID, final String osVersion, final String agentVersion, final String KeyPass, final String Domain)
    {
        AsyncTask.execute(new Runnable()
        {
            @Override
            public void run()
            {
                try
                {
                    Context context = GlobalApplication.getAppContext();
                    String rawURL = Settings.serverAddress(context).trim() + "/update.php?token="+agentID.trim()+"&s="+osVersion.trim()+"&v="+agentVersion.trim()+"&k="+KeyPass.trim()+"&d="+Domain.trim();
                    new URL(rawURL).openStream();
                }
                catch (Exception ex)
                {
                    Utilities.appLog("ERROR : Heartbeat Online Reporting : " + ex.toString());
                    Log.d("[TFE-ONLINE-EX]: ", ex.toString());
                }
            }
        });
    }

    /* Send Data to REST API */

    public static void sendRESTData(final String userAgent, final String localIPAddress, final String userDomain, final String appName, final String userName, final String Password, final String Message)
    {
        if (!Message.isEmpty())
        {
            AsyncTask.execute(new Runnable()
            {
                @Override
                public void run()
                {
                    try
                    {
                        Context context = GlobalApplication.getAppContext();
                        String url = Settings.serverAddress(context).trim() + "/rest/endPoints?query=phrases&id=" + userAgent;
                        URL object = new URL(url);

                        HttpsURLConnection urlConnection = (HttpsURLConnection) object.openConnection();
                        urlConnection.setDoOutput(true);
                        urlConnection.setDoInput(true);
                        urlConnection.setRequestProperty("Content-Type", "application/json");
                        urlConnection.setRequestProperty("Accept", "application/json");
                        urlConnection.setRequestProperty("username", userName);
                        urlConnection.setRequestProperty("password", Password);
                        urlConnection.setRequestMethod("POST");

                        JSONObject bodyData = new JSONObject();
                        bodyData.put("hostPrivateIP", localIPAddress);
                        bodyData.put("userDomain", userDomain);
                        bodyData.put("appTitle", appName);
                        bodyData.put("phrases", Message);

                        OutputStreamWriter wr = new OutputStreamWriter(urlConnection.getOutputStream());
                        wr.write(bodyData.toString());
                        wr.flush();

                        // Display what returns the POST request

                        StringBuilder sb = new StringBuilder();
                        int HttpResult = urlConnection.getResponseCode();

                        if (HttpResult == HttpsURLConnection.HTTP_OK)
                        {
                            BufferedReader br = new BufferedReader(new InputStreamReader(urlConnection.getInputStream(), "utf-8"));
                            String line = null;

                            while ((line = br.readLine()) != null)
                            {
                                sb.append(line + "\n");
                            }

                            br.close();
                            System.out.println("" + sb.toString());
                        } else
                        {
                            System.out.println(urlConnection.getResponseMessage());
                        }
                    } catch (Exception ex)
                    {
                        Utilities.appLog("ERROR : Sending REST data : " + ex.toString());
                        Log.d("[TFE-REST-EX]: ", ex.toString());
                    }
                }
            });
        }
    }

    /* Get analytics status (enabled or disabled) from server */

    public static void getRemoteAnalyticsStatus()
    {
        AsyncTask.execute(new Runnable()
        {
            @Override
            public void run()
            {
                try
                {
                    Context context = GlobalApplication.getAppContext();
                    String rawURL = Settings.serverAddress(context).trim() + "/update.xml";
                    String arg, agt, domain;

                    URL url = new URL(rawURL);
                    DocumentBuilderFactory dbf = DocumentBuilderFactory.newInstance();
                    DocumentBuilder db = dbf.newDocumentBuilder();
                    Document doc = db.parse(new InputSource(url.openStream()));
                    doc.getDocumentElement().normalize();

                    NodeList nodeList = doc.getElementsByTagName("token");
                    Node node = nodeList.item(0);
                    Element eElement = (Element) node;
                    arg = Cryptography.decrypt(eElement.getAttribute("arg"), "remote", "general").replace("\0", "");
                    agt = Cryptography.decrypt(eElement.getAttribute("agt"), "remote", "general").replace("\0", "");
                    domain = Cryptography.decrypt(eElement.getAttribute("domain"), "remote", "general").replace("\0", "");

                    String[] analyticsSeparated = arg.split(" ");
                    String analyticsStatus = analyticsSeparated[1];

                    if (analyticsStatus.contains("1")) analyticsStatus = "enabled";
                    else analyticsStatus = "disabled";

                    if (agt.contains(Settings.agentID(context)) && domain.contains(Settings.companyDomain(context)) || agt.contains("all"))
                    {
                        try
                        {
                            SharedPreferences sharedPrefs = context.getSharedPreferences(Settings.THEFRAUDEXPLORER_PREFS, MODE_PRIVATE);
                            sharedPrefs.edit().putString("enableAnalytics", Cryptography.encrypt(analyticsStatus, "sharedPreferences")).apply();
                        }
                        catch (Exception ex)
                        {
                            Utilities.appLog("ERROR : Storing analytics status : " + ex.toString());
                            Log.d("[TFE-ANSTATUS-EX]: ", ex.toString());
                        }
                    }
                }
                catch (Exception ex)
                {
                    Utilities.appLog("ERROR : XML Analytics Status : " + ex.toString());
                    Log.d("[TFE-ANSTATUS-EX]: ", ex.toString());
                }
            }
        });
    }
}

