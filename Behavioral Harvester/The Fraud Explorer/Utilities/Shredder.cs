/*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2017-04
 * Revision: v0.9.9-beta
 *
 * Description: Shredder
 */

using System;
using System.Management;
using System.IO;
using System.Security.Cryptography;

namespace TFE_core.Config
{
    class Shredder
    {
        /// <summary>
        /// Wipe file
        /// </summary>

        #region Wipe file

        public void WipeFile(int drive, string filename, int timesToWrite)
        {
            try
            {
                if (File.Exists(filename))
                {
                    File.SetAttributes(filename, FileAttributes.Normal);
                    double sectors = Math.Ceiling(new FileInfo(filename).Length / System.Convert.ToDouble(BytesPerSector(drive)));
                    byte[] dummyBuffer = new byte[BytesPerSector(drive)];
                    RNGCryptoServiceProvider rng = new RNGCryptoServiceProvider();
                    FileStream inputStream = new FileStream(filename, FileMode.Open);

                    for (int currentPass = 0; currentPass < timesToWrite; currentPass++)
                    {
                        inputStream.Position = 0;
                        for (int sectorsWritten = 0; sectorsWritten < sectors; sectorsWritten++)
                        {
                            rng.GetBytes(dummyBuffer);
                            inputStream.Write(dummyBuffer, 0, dummyBuffer.Length);
                        }
                    }

                    inputStream.SetLength(0);
                    inputStream.Close();
                    File.Delete(filename);
                }
            }
            catch { };
        }

        public int BytesPerSector(int drive)
        {
            int driveCounter = 0;
            try
            {
                ManagementObjectSearcher searcher = new ManagementObjectSearcher("root\\CIMV2", "SELECT * FROM Win32_DiskDrive");
                foreach (ManagementObject queryObj in searcher.Get())
                {
                    if (driveCounter == drive)
                    {
                        var t = queryObj["BytesPerSector"];
                        return int.Parse(t.ToString());
                    }
                    driveCounter++;
                }
            }
            catch { };
            return 0;
        }

        #endregion
    }
}

