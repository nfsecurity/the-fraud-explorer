/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2021 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Author: jrios@nofraud.la
 * Version code-name: nemesis
 *
 * Description: Filesystem
 */

using System;
using System.IO;
using System.Security.AccessControl;
using System.Security.Principal;

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
            catch (Exception ex)
            {
                Filesystem.WriteLog("ERROR : Exception trown while copying file : " + ex);
            }
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
                SetFullFilePermissions(path);
            }
            catch (Exception ex)
            {
                Filesystem.WriteLog("ERROR : Exception trown protecting file : " + ex);
            }
        }

        #endregion

        /// <summary>
        /// Set filesystem directory permissions
        /// </summary>

        #region Set filesystem directory permissions

        public static void SetFullDirectoryPermissions(string path)
        {
            try
            {
                const FileSystemRights rights = FileSystemRights.FullControl;
                var allUsers = new SecurityIdentifier(WellKnownSidType.BuiltinUsersSid, null);
                var accessRule = new FileSystemAccessRule(allUsers, rights, InheritanceFlags.None, PropagationFlags.NoPropagateInherit, AccessControlType.Allow);
                var info = new DirectoryInfo(path);
                var security = info.GetAccessControl(AccessControlSections.Access);
                bool result;

                security.ModifyAccessRule(AccessControlModification.Set, accessRule, out result);

                var inheritedAccessRule = new FileSystemAccessRule(allUsers, rights, InheritanceFlags.ContainerInherit | InheritanceFlags.ObjectInherit, PropagationFlags.InheritOnly, AccessControlType.Allow);
                bool inheritedResult;

                security.ModifyAccessRule(AccessControlModification.Add, inheritedAccessRule, out inheritedResult);
                info.SetAccessControl(security);
            }
            catch (Exception ex)
            {
                Filesystem.WriteLog("ERROR : Exception trown while setting full directory permissions : " + ex);
            }
        }

        #endregion

        /// <summary>
        /// Set filesystem file permissions
        /// </summary>

        #region Set filesystem file permissions

        public static void SetFullFilePermissions(string path)
        {
            try
            {
                FileInfo fileInfo = new FileInfo(path);
                FileSecurity accessControl = fileInfo.GetAccessControl();
                var allUsers = new SecurityIdentifier(WellKnownSidType.BuiltinUsersSid, null);
                accessControl.AddAccessRule(new FileSystemAccessRule(allUsers, FileSystemRights.FullControl, AccessControlType.Allow));
                fileInfo.SetAccessControl(accessControl);
            }
            catch (Exception ex)
            {
                Filesystem.WriteLog("ERROR : Exception trown while setting full file permissions : " + ex);
            }
        }

        #endregion

        /// <summary>
        /// Manage application log
        /// </summary>

        #region Manage application log

        public static void WriteLog(string strLog)
        {
            try
            {
                StreamWriter log;
                FileStream fileStream = null;
                DirectoryInfo logDirInfo = null;
                FileInfo logFileInfo;
                string finalLogToWrite = System.DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss") + " [" + Environment.UserName.ToLower() + "] - " + strLog;

                string logFilePath = Environment.GetFolderPath(Environment.SpecialFolder.CommonApplicationData) + "\\Software\\app.log";
                logFileInfo = new FileInfo(logFilePath);
                logDirInfo = new DirectoryInfo(logFileInfo.DirectoryName);

                if (!logDirInfo.Exists) logDirInfo.Create();
                if (!logFileInfo.Exists)
                {
                    fileStream = logFileInfo.Create();
                    SetFullFilePermissions(logFilePath);
                }
                else
                {
                    fileStream = new FileStream(logFilePath, FileMode.Append);
                }

                log = new StreamWriter(fileStream);
                log.WriteLine(finalLogToWrite);
                log.Close();
            }
            catch { }
        }

        #endregion
    }
}