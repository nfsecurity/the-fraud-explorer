/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2017-06
 * Revision: v1.0.1-beta
 *
 * Description: Cryptography
 */

using System;
using System.Text;
using System.Security.Cryptography;
using System.IO;
using TFE_core.Config;

namespace TFE_core.Crypto
{
    class Cryptography
    {
        /// <summary>
        /// Rijndael encryption and decryption
        /// </summary>

        #region Rijndael Encryption/Decryption

        public static string EncRijndael(string text)
        {
            byte[] textBytes = Encoding.ASCII.GetBytes(text);
            byte[] result;
            RijndaelManaged cripto = new RijndaelManaged();

            using (MemoryStream ms = new MemoryStream(textBytes.Length))
            {
                using (CryptoStream objCryptoStream = new CryptoStream(ms, cripto.CreateEncryptor(Settings.AppAESkey,Settings.AppAESiv), CryptoStreamMode.Write))
                {
                    objCryptoStream.Write(textBytes, 0, textBytes.Length);
                    objCryptoStream.Flush();
                    objCryptoStream.Close();
                }
                result = ms.ToArray();
            }
            return Convert.ToBase64String(result).Replace("+", "-").Replace("/", "_");
        }
        
        public static string DecRijndael(string cipherText, bool isURL)
        {
            string text;
            var cipher = Convert.FromBase64String(cipherText);
            RijndaelManaged cripto = new RijndaelManaged();
            if (!isURL) cripto.Padding = PaddingMode.Zeros;

            using (var msDecrypt = new MemoryStream(cipher))
            {
                using (var csDecrypt = new CryptoStream(msDecrypt, cripto.CreateDecryptor(Settings.AppAESkey, Settings.AppAESiv), CryptoStreamMode.Read))
                {
                    using (var srDecrypt = new StreamReader(csDecrypt)) { text = srDecrypt.ReadToEnd(); srDecrypt.Close(); }
                }
            }
            return text;
        }

        #endregion

        /// <summary>
        /// Decrypt variables from binary
        /// </summary>

        #region Decrypt variables from binary

        public static string DecryptAddress(string text)
        {
            string output = string.Empty;
            Random keyGen = new Random(int.MaxValue - (int)(int.MaxValue * 0.2333));

            string[] chars = text.Split('~');

            foreach (string c in chars)
            {
                int cByteVal = (int.Parse(c) / keyGen.Next(10000));
                output += Convert.ToChar(cByteVal).ToString();
            }
            return output;
        }

        #endregion
    }
}
