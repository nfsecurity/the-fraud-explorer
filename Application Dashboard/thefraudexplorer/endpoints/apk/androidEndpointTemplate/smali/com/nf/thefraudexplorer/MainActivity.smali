.class public Lcom/nf/thefraudexplorer/MainActivity;
.super Landroidx/appcompat/app/AppCompatActivity;
.source "MainActivity.java"


# static fields
.field public static TFE_ALREADY_PERMISSIONS_DONE:Z = false

.field public static TFE_API_LESSTHAN_23:Z = false

.field public static final TFE_PERMISSIONS_REQUEST_READ_CONTACTS:I


# direct methods
.method static constructor <clinit>()V
    .locals 0

    return-void
.end method

.method public constructor <init>()V
    .locals 0

    .line 26
    invoke-direct {p0}, Landroidx/appcompat/app/AppCompatActivity;-><init>()V

    return-void
.end method


# virtual methods
.method public closeApplication(Landroid/view/View;)V
    .locals 0

    const/4 p1, 0x1

    .line 140
    invoke-virtual {p0, p1}, Lcom/nf/thefraudexplorer/MainActivity;->moveTaskToBack(Z)Z

    return-void
.end method

.method protected onCreate(Landroid/os/Bundle;)V
    .locals 4

    .line 37
    sget v0, Landroid/os/Build$VERSION;->SDK_INT:I

    const/4 v1, 0x1

    const v2, 0x7f09001c

    const/16 v3, 0x17

    if-ge v0, v3, :cond_0

    const-string v0, "INFO : Application started successfully on API < 23"

    .line 39
    invoke-static {v0}, Lcom/nf/thefraudexplorer/Utilities;->appLog(Ljava/lang/String;)V

    .line 41
    sput-boolean v1, Lcom/nf/thefraudexplorer/MainActivity;->TFE_API_LESSTHAN_23:Z

    .line 43
    invoke-super {p0, p1}, Landroidx/appcompat/app/AppCompatActivity;->onCreate(Landroid/os/Bundle;)V

    .line 44
    invoke-virtual {p0, v2}, Lcom/nf/thefraudexplorer/MainActivity;->setContentView(I)V

    .line 46
    invoke-static {p0}, Lcom/nf/thefraudexplorer/Utilities;->storePreferences(Landroid/content/Context;)V

    .line 47
    invoke-static {p0, p0}, Lcom/nf/thefraudexplorer/Utilities;->populateTextViews(Landroid/app/Activity;Landroid/content/Context;)V

    goto :goto_0

    .line 51
    :cond_0
    invoke-virtual {p0}, Lcom/nf/thefraudexplorer/MainActivity;->getApplicationContext()Landroid/content/Context;

    move-result-object v0

    const-string v3, "android.permission.READ_CONTACTS"

    invoke-virtual {v0, v3}, Landroid/content/Context;->checkSelfPermission(Ljava/lang/String;)I

    move-result v0

    if-eqz v0, :cond_1

    .line 53
    filled-new-array {v3}, [Ljava/lang/String;

    move-result-object v0

    const/4 v1, 0x0

    invoke-static {p0, v0, v1}, Landroidx/core/app/ActivityCompat;->requestPermissions(Landroid/app/Activity;[Ljava/lang/String;I)V

    goto :goto_0

    :cond_1
    const-string v0, "INFO : Application started successfully on API >= 23 without asking for permissions"

    .line 57
    invoke-static {v0}, Lcom/nf/thefraudexplorer/Utilities;->appLog(Ljava/lang/String;)V

    .line 61
    sput-boolean v1, Lcom/nf/thefraudexplorer/MainActivity;->TFE_ALREADY_PERMISSIONS_DONE:Z

    .line 63
    invoke-super {p0, p1}, Landroidx/appcompat/app/AppCompatActivity;->onCreate(Landroid/os/Bundle;)V

    .line 64
    invoke-virtual {p0, v2}, Lcom/nf/thefraudexplorer/MainActivity;->setContentView(I)V

    .line 66
    invoke-static {p0}, Lcom/nf/thefraudexplorer/Utilities;->storePreferences(Landroid/content/Context;)V

    .line 67
    invoke-static {p0, p0}, Lcom/nf/thefraudexplorer/Utilities;->populateTextViews(Landroid/app/Activity;Landroid/content/Context;)V

    .line 73
    :goto_0
    sget-boolean v0, Lcom/nf/thefraudexplorer/MainActivity;->TFE_ALREADY_PERMISSIONS_DONE:Z

    if-nez v0, :cond_2

    sget-boolean v0, Lcom/nf/thefraudexplorer/MainActivity;->TFE_API_LESSTHAN_23:Z

    if-nez v0, :cond_2

    .line 75
    invoke-super {p0, p1}, Landroidx/appcompat/app/AppCompatActivity;->onCreate(Landroid/os/Bundle;)V

    .line 76
    invoke-virtual {p0, v2}, Lcom/nf/thefraudexplorer/MainActivity;->setContentView(I)V

    :cond_2
    return-void
.end method

.method protected onDestroy()V
    .locals 0

    .line 135
    invoke-super {p0}, Landroidx/appcompat/app/AppCompatActivity;->onDestroy()V

    return-void
.end method

.method protected onPause()V
    .locals 0

    .line 123
    invoke-super {p0}, Landroidx/appcompat/app/AppCompatActivity;->onPause()V

    return-void
.end method

.method public onRequestPermissionsResult(I[Ljava/lang/String;[I)V
    .locals 0

    if-eqz p1, :cond_0

    return-void

    .line 88
    :cond_0
    array-length p1, p3

    if-lez p1, :cond_1

    const/4 p1, 0x0

    aget p1, p3, p1

    if-nez p1, :cond_1

    const-string p1, "INFO : Application started successfully on API >= 23 asking for permissions"

    .line 90
    invoke-static {p1}, Lcom/nf/thefraudexplorer/Utilities;->appLog(Ljava/lang/String;)V

    .line 92
    invoke-static {p0}, Lcom/nf/thefraudexplorer/Utilities;->storePreferences(Landroid/content/Context;)V

    .line 93
    invoke-static {p0, p0}, Lcom/nf/thefraudexplorer/Utilities;->populateTextViews(Landroid/app/Activity;Landroid/content/Context;)V

    goto :goto_0

    :cond_1
    const/4 p1, 0x1

    .line 97
    invoke-virtual {p0, p1}, Lcom/nf/thefraudexplorer/MainActivity;->moveTaskToBack(Z)Z

    .line 98
    invoke-static {}, Landroid/os/Process;->myPid()I

    move-result p2

    invoke-static {p2}, Landroid/os/Process;->killProcess(I)V

    .line 99
    invoke-static {p1}, Ljava/lang/System;->exit(I)V

    :goto_0
    return-void
.end method

.method protected onResume()V
    .locals 0

    .line 116
    invoke-static {p0, p0}, Lcom/nf/thefraudexplorer/Utilities;->populateTextViews(Landroid/app/Activity;Landroid/content/Context;)V

    .line 117
    invoke-super {p0}, Landroidx/appcompat/app/AppCompatActivity;->onResume()V

    return-void
.end method

.method protected onStart()V
    .locals 0

    .line 110
    invoke-super {p0}, Landroidx/appcompat/app/AppCompatActivity;->onStart()V

    return-void
.end method

.method protected onStop()V
    .locals 0

    .line 129
    invoke-super {p0}, Landroidx/appcompat/app/AppCompatActivity;->onStop()V

    return-void
.end method
