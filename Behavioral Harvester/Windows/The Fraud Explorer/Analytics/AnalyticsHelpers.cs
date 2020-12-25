/*Version code-name: nemesis
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
 * Description: Analytics Helpers
 */

using System;
using System.Collections.Concurrent;
using System.Globalization;
using System.Linq;
using System.Text;
using TFE_core.Database;

namespace TFE_core.Analytics
{
    class AnalyticsHelpers { }

    public static class TextHelpers
    {
        /// <summary>
        /// Remove Diacritics
        /// </summary>

        #region Remove Diacritics

        public static string RemoveDiacritics(this string text)
        {
            if (string.IsNullOrWhiteSpace(text)) return text;
            text = text.Normalize(NormalizationForm.FormD);
            var chars = text.Where(c => CharUnicodeInfo.GetUnicodeCategory(c) != UnicodeCategory.NonSpacingMark).ToArray();
            return new string(chars).Normalize(NormalizationForm.FormC);
        }

        #endregion

        /// <summary>
        /// Words Sanitizer
        /// </summary>

        #region Words Sanitizer

        public static string[] excludeWords = { "www", "http", "ftp", "notepad", "cmd", "calc", "msconfig", "dxdiag", "cleanmgr", "regedit", "iexplore", "mspaint", "resmon", "sysedit", "taskmgr", "winver", "explorer",
                                                "ipconfig", "pbrush", "perfmon", "telnet", "certmgr", "dxdiag", "mmc", "mspaint", "services", "msinfo", "taskschd" };

        public static bool WordsSanitizer(string text)
        {
            string sourceWord = TextHelpers.RemoveDiacritics(text).ToLower();

            foreach (string Word in excludeWords)
            {
                if (sourceWord.IndexOf(Word) == -1) continue;
                else return false;
            }
            return true;
        }

        #endregion

        /// <summary>
        /// Control keys Sanitizer
        /// </summary>

        #region Control keys Sanitizer

        public static string[] excludeKeys = { "Add", "Apps", "Attn", "CapsLock", "Delete", "Divide", "Down", "End", "Escape", "Help", "Home", "Insert", "LeftAlt", "LeftControl", "LeftShift", "LeftWindows", "Left",
                                               "Multiply", "NumLock", "PageDown", "PageUp", "Pause", "Play", "Print", "PrintScreen", "RightAlt", "RightAlt", "RightShift", "RightWindows", "Scroll", "Select", "Right",
                                               "Separator", "Sleep", "Subtract", "VolumeDown", "VolumeMute", "VolumeUp", "Zoom", "Up", "F1", "F2", "F3", "F4", "F5", "F6", "F7", "F8", "F9", "F10", "F11", "F12", "F13", "F14",
                                               "F15", "F16", "F17", "F18", "F19", "F20", "F21", "F22", "F23", "F24", "NumPad0", "NumPad1", "NumPad2", "NumPad3", "NumPad4", "NumPad5", "NumPad6", "NumPad7", "NumPad8", "NumPad9",
                                               "OemClear", "OemCloseBrackets", "OemCopy", "OemMinus", "OemOpenBrackets", "OemPipe", "OemSemicolon", "OemTilde", "Oem1", "Oem2", "Oem3", "Oem4", "Oem5", "Oem6", "Oem7", "Oem8",
                                               "Oem9", "D0", "D1", "D2", "D3", "D4", "D5", "D6", "D7", "D8", "D9", "Capital", "Shift", "Cancel", "ShiftKey", "Control", "ControlKey"
                                             };

        public static bool KeysSanitizer(string sourceKey)
        {
            foreach (string Key in excludeKeys)
            {
                if (sourceKey.IndexOf(Key) == -1) continue;
                else return false;
            }
            return true;
        }

        #endregion

        /// <summary>
        /// Excluded Apps
        /// </summary>

        #region Excluded Apps

        public static string appsInventory = SQLStorage.RetrievePar("onlyApps").ToLower();
        
        public static bool AppsValidation(string activeApp)
        {
            if (appsInventory.Equals("OnlyAppsAll")) return true;
            else if (activeApp.ToLower().Contains(appsInventory)) return true;
            else return false;
        }

        #endregion
    }
}