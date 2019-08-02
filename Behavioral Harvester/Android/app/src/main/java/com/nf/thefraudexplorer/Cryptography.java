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
 * Description: Cryptography
 */

package com.nf.thefraudexplorer;

import android.content.Context;
import android.util.Base64;
import android.util.Log;
import javax.crypto.Cipher;
import javax.crypto.spec.IvParameterSpec;
import javax.crypto.spec.SecretKeySpec;

public class Cryptography
{
    /* Encrypt Data */

    public static String encrypt(String value)
    {
        try
        {
            Context context = GlobalApplication.getAppContext();
            String key = Settings.cipherKey(context);
            String initVector = Settings.cipherKey(context);

            IvParameterSpec iv = new IvParameterSpec(initVector.getBytes("UTF-8"));
            SecretKeySpec skeySpec = new SecretKeySpec(key.getBytes("UTF-8"), "AES");

            Cipher cipher = Cipher.getInstance("AES/CBC/PKCS5PADDING");
            cipher.init(Cipher.ENCRYPT_MODE, skeySpec, iv);

            byte[] encrypted = cipher.doFinal(value.getBytes());

            return Base64.encodeToString(encrypted, Base64.DEFAULT).replace("+","-").replace("/","_");
        }
        catch (Exception ex)
        {
            Utilities.appLog("ERROR : Encrypting data : " + ex.toString());
            Log.d("[THEFRAUDEXPLORER_EX]: ", ex.toString());

            return null;
        }
    }

    /* Decrypt Data */

    public static String decrypt(String encrypted)
    {
        try
        {
            Context context = GlobalApplication.getAppContext();
            String key = Settings.cipherKey(context);
            String initVector = Settings.cipherKey(context);

            IvParameterSpec iv = new IvParameterSpec(initVector.getBytes("UTF-8"));
            SecretKeySpec skeySpec = new SecretKeySpec(key.getBytes("UTF-8"), "AES");

            Cipher cipher = Cipher.getInstance("AES/CBC/PKCS5PADDING");
            cipher.init(Cipher.DECRYPT_MODE, skeySpec, iv);
            byte[] original = cipher.doFinal(Base64.decode(encrypted, Base64.DEFAULT));

            return new String(original);
        }
        catch (Exception ex)
        {
            Utilities.appLog("ERROR : Decrypting data : " + ex.toString());
            Log.d("[THEFRAUDEXPLORER_EX]: ", ex.toString());

            return null;
        }
    }
}
