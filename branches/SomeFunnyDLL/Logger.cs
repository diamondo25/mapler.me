using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.IO;

namespace System
{
    public static class Logger
    {
        public static string Logfile { get; private set; }
        public static string PacketLogfile { get; private set; }

        public static bool PacketLogging { get; private set; }

        public static void SetLogfile(bool pPacketLogging)
        {
            PacketLogging = pPacketLogging;
            Logfile = "logs" + Path.DirectorySeparatorChar + "Log" + DateTime.Now.ToString("yyyy_M_d__hh_mm_ss") + ".log";
            if (PacketLogging)
                PacketLogfile = "logs" + Path.DirectorySeparatorChar + "PacketLog" + DateTime.Now.ToString("yyyy_M_d__hh_mm_ss") + ".log";

            if (!Directory.Exists("logs"))
                Directory.CreateDirectory("logs");
        }

        public static void WriteLine(string pInput, params object[] pParams)
        {
            string text = string.Format("[{0}] {1} {2}", DateTime.Now, string.Format(pInput, pParams), Environment.NewLine);
            try
            {
                File.AppendAllText(Logfile, text);
            }
            catch { }
            Console.Write(text);
        }

        public static void Write(string pInput, params object[] pParams)
        {
            try
            {
                File.AppendAllText(Logfile, string.Format(pInput, pParams));
            }
            catch { }
            Console.Write(pInput, pParams);
        }

        public static void ErrorLog(string pInput, params object[] pParams)
        {
            Console.WriteLine("---------- EXCEPTION -----------");
            Console.WriteLine(pInput, pParams);
            try
            {
                File.AppendAllText("EXCEPTIONS.txt", string.Format("{0}[{2}]{1}{0}", Environment.NewLine, "-----------------", DateTime.Now) + string.Format(pInput, pParams) + Environment.NewLine);
            }
            catch { }
        }

        public static void PWrite(string pInput, params object[] pParams)
        {
            if (!PacketLogging)
                return;
            File.AppendAllText(PacketLogfile, string.Format(pInput, pParams));
        }
    }
}
