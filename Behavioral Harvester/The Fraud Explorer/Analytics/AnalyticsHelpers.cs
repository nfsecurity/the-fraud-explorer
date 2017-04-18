/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2017-04
 * Revision: v1.0.0-beta
 *
 * Description: Analytics Helpers
 */

using System.Globalization;
using System.Linq;
using System.Text;

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
                                                "ipconfig", "pbrush", "perfmon", "telnet"
                                              };

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

        public static string[] excludeKeys = { "Add", "Apps", "Attn", "Back", "CapsLock", "Delete", "Divide", "Down", "End", "Escape", "Help", "Home", "Insert", "Left", "LeftAlt", "LeftControl", "LeftShift", "LeftWindows",
                                               "Multiply", "NumLock", "PageDown", "PageUp", "Pause", "Play", "Print", "PrintScreen", "Right", "RightAlt", "RightAlt", "RightShift", "RightWindows", "Scroll", "Select",
                                               "Separator", "Sleep", "Subtract", "VolumeDown", "VolumeMute", "VolumeUp", "Zoom", "Up", "F1", "F2", "F3", "F4", "F5", "F6", "F7", "F8", "F9", "F10", "F11", "F12", "F13", "F14",
                                               "F15", "F16", "F17", "F18", "F19", "F20", "F21", "F22", "F23", "F24", "NumPad0", "NumPad1", "NumPad2", "NumPad3", "NumPad4", "NumPad5", "NumPad6", "NumPad7", "NumPad8", "NumPad9",
                                               "OemClear", "OemCloseBrackets", "OemComma", "OemCopy", "OemMinus", "OemOpenBrackets", "OemPeriod", "OemPipe", "OemPlus", "OemQuestion", "OemQuotes", "OemSemicolon", "OemTilde",
                                               "Oem1", "Oem2", "Oem3", "Oem4", "Oem5", "Oem6", "Oem7", "Oem8", "Oem9", "D0", "D1", "D2", "D3", "D4", "D5", "D6", "D7", "D8", "D9", "Capital", "Shift", "Cancel", "ShiftKey",
                                               "Control", "ControlKey"
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
    }
}