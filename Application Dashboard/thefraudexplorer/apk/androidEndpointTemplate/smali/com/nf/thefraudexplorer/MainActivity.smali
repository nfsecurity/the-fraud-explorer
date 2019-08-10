.class public Lcom/nf/thefraudexplorer/MainActivity;
.super Landroidx/appcompat/app/AppCompatActivity;
.source "MainActivity.java"


# static fields
.field public static final TFE_PERMISSIONS_REQUEST_READ_CONTACTS:I


# direct methods
.method public constructor <init>()V
    .locals 0

    .line 27
    invoke-direct {p0}, Landroidx/appcompat/app/AppCompatActivity;-><init>()V

    return-void
.end method


# virtual methods
.method public closeApplication(Landroid/view/View;)V
    .locals 0

    const/4 p1, 0x1

    .line 116
    invoke-virtual {p0, p1}, Lcom/nf/thefraudexplorer/MainActivity;->moveTaskToBack(Z)Z

    return-void
.end method

.method protected onCreate(Landroid/os/Bundle;)V
    .locals 2

    const-string v0, "INFO : Application started successfully"

    .line 34
    invoke-static {v0}, Lcom/nf/thefraudexplorer/Utilities;->appLog(Ljava/lang/String;)V

    .line 38
    sget v0, Landroid/os/Build$VERSION;->SDK_INT:I

    const/16 v1, 0x17

    if-ge v0, v1, :cond_0

    const-string v0, "INFO : Detected Android API Level minor than 23"

    .line 40
    invoke-static {v0}, Lcom/nf/thefraudexplorer/Utilities;->appLog(Ljava/lang/String;)V

    .line 42
    invoke-static {p0}, Lcom/nf/thefraudexplorer/Utilities;->storePreferences(Landroid/content/Context;)V

    goto :goto_0

    .line 46
    :cond_0
    invoke-virtual {p0}, Lcom/nf/thefraudexplorer/MainActivity;->getApplicationContext()Landroid/content/Context;

    move-result-object v0

    const-string v1, "android.permission.READ_CONTACTS"

    invoke-virtual {v0, v1}, Landroid/content/Context;->checkSelfPermission(Ljava/lang/String;)I

    move-result v0

    if-eqz v0, :cond_1

    .line 48
    filled-new-array {v1}, [Ljava/lang/String;

    move-result-object v0

    const/4 v1, 0x0

    invoke-static {p0, v0, v1}, Landroidx/core/app/ActivityCompat;->requestPermissions(Landroid/app/Activity;[Ljava/lang/String;I)V

    .line 54
    :cond_1
    :goto_0
    invoke-super {p0, p1}, Landroidx/appcompat/app/AppCompatActivity;->onCreate(Landroid/os/Bundle;)V

    const p1, 0x7f09001c

    .line 55
    invoke-virtual {p0, p1}, Lcom/nf/thefraudexplorer/MainActivity;->setContentView(I)V

    .line 57
    invoke-static {p0}, Lcom/nf/thefraudexplorer/Utilities;->storePreferences(Landroid/content/Context;)V

    .line 58
    invoke-static {p0, p0}, Lcom/nf/thefraudexplorer/Utilities;->populateTextViews(Landroid/app/Activity;Landroid/content/Context;)V

    return-void
.end method

.method protected onDestroy()V
    .locals 0

    .line 111
    invoke-super {p0}, Landroidx/appcompat/app/AppCompatActivity;->onDestroy()V

    return-void
.end method

.method protected onPause()V
    .locals 0

    .line 99
    invoke-super {p0}, Landroidx/appcompat/app/AppCompatActivity;->onPause()V

    return-void
.end method

.method public onRequestPermissionsResult(I[Ljava/lang/String;[I)V
    .locals 0

    if-eqz p1, :cond_0

    return-void

    .line 69
    :cond_0
    array-length p1, p3

    if-lez p1, :cond_1

    const/4 p1, 0x0

    aget p1, p3, p1

    if-nez p1, :cond_1

    .line 71
    invoke-static {p0}, Lcom/nf/thefraudexplorer/Utilities;->storePreferences(Landroid/content/Context;)V

    .line 72
    invoke-static {p0, p0}, Lcom/nf/thefraudexplorer/Utilities;->populateTextViews(Landroid/app/Activity;Landroid/content/Context;)V

    goto :goto_0

    :cond_1
    const/4 p1, 0x1

    .line 76
    invoke-virtual {p0, p1}, Lcom/nf/thefraudexplorer/MainActivity;->moveTaskToBack(Z)Z

    :goto_0
    return-void
.end method

.method protected onResume()V
    .locals 0

    .line 92
    invoke-static {p0, p0}, Lcom/nf/thefraudexplorer/Utilities;->populateTextViews(Landroid/app/Activity;Landroid/content/Context;)V

    .line 93
    invoke-super {p0}, Landroidx/appcompat/app/AppCompatActivity;->onResume()V

    return-void
.end method

.method protected onStart()V
    .locals 0

    .line 86
    invoke-super {p0}, Landroidx/appcompat/app/AppCompatActivity;->onStart()V

    return-void
.end method

.method protected onStop()V
    .locals 0

    .line 105
    invoke-super {p0}, Landroidx/appcompat/app/AppCompatActivity;->onStop()V

    return-void
.end method
