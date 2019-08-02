.class public Lcom/nf/thefraudexplorer/GlobalApplication;
.super Landroid/app/Application;
.source "GlobalApplication.java"


# static fields
.field private static appContext:Landroid/content/Context;


# direct methods
.method public constructor <init>()V
    .locals 0

    .line 21
    invoke-direct {p0}, Landroid/app/Application;-><init>()V

    return-void
.end method

.method public static getAppContext()Landroid/content/Context;
    .locals 1

    .line 34
    sget-object v0, Lcom/nf/thefraudexplorer/GlobalApplication;->appContext:Landroid/content/Context;

    return-object v0
.end method


# virtual methods
.method public onCreate()V
    .locals 1

    .line 28
    invoke-super {p0}, Landroid/app/Application;->onCreate()V

    .line 29
    invoke-virtual {p0}, Lcom/nf/thefraudexplorer/GlobalApplication;->getApplicationContext()Landroid/content/Context;

    move-result-object v0

    sput-object v0, Lcom/nf/thefraudexplorer/GlobalApplication;->appContext:Landroid/content/Context;

    return-void
.end method
