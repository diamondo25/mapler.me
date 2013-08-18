using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.IO;

namespace System
{
    public partial class Logger
    {
        public static string Logfile { get; private set; }
        public static string PacketLogfile { get; private set; }
        public const string Version = "Mapler.me Software V1.0.2.0";

        public static bool PacketLogging { get; private set; }
        private static bool _disabledLogging;

        public static void SetLogfile(bool pPacketLogging, bool pDisabled = false)
        {
            _disabledLogging = pDisabled;
            PacketLogging = pPacketLogging;

            if (_disabledLogging) return;

            Logfile = "logs" + Path.DirectorySeparatorChar + "Log" + DateTime.Now.ToString("yyyy_M_d__hh_mm_ss") + ".log";
            if (PacketLogging)
                PacketLogfile = "logs" + Path.DirectorySeparatorChar + "PacketLog" + DateTime.Now.ToString("yyyy_M_d__hh_mm_ss") + ".log";

            if (!Directory.Exists("logs"))
                Directory.CreateDirectory("logs");

            WriteLine("INITIALIZED {0}", Version);
        }

        public static void WriteLine(string pInput, params object[] pParams)
        {
            if (_disabledLogging) return;
            string text = string.Format("[{0}] {1} {2}", DateTime.Now, string.Format(pInput, pParams), Environment.NewLine);
            Console.Write(text);

            try
            {
                using (var sw = new StreamWriter(File.Open(Logfile, FileMode.Append, FileAccess.Write, FileShare.Write)))
                    sw.Write(text);
            }
            catch { }
        }

        public static void Write(string pInput, params object[] pParams)
        {
            if (_disabledLogging) return;
            Console.Write(pInput, pParams);

            try
            {
                using (var sw = new StreamWriter(File.Open(Logfile, FileMode.Append, FileAccess.Write, FileShare.Write)))
                    sw.Write(string.Format(pInput, pParams));
            }
            catch { }
        }

        public static void ErrorLog(string pInput, params object[] pParams)
        {
            if (_disabledLogging) return;

            WriteLine("---------- EXCEPTION -----------");
            WriteLine(pInput, pParams);
            try
            {
                File.AppendAllText("EXCEPTIONS.txt", string.Format("{0}[{2}]{1}{0}", Environment.NewLine, "-----------------", DateTime.Now) + string.Format(pInput, pParams) + Environment.NewLine);
            }
            catch { }
        }

        public static void PWrite(string pInput, params object[] pParams)
        {
            if (_disabledLogging) return;

            if (!PacketLogging)
                return;

            try
            {
                using (var sw = new StreamWriter(File.Open(Logfile, FileMode.Append, FileAccess.ReadWrite, FileShare.ReadWrite)))
                    sw.Write(string.Format(pInput, pParams));
            }
            catch { }
        }
    }
}
