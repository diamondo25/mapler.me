using System;
using System.Collections.Generic;
using System.Linq;
using System.Windows.Forms;

namespace Mapler_Client
{
    static class Program
    {
        public static int SENT_PACKETS = 0, SENT_DATA = 0;
        public static int RECV_PACKETS = 0, RECV_DATA = 0;

        public static bool Closing = false;

        /// <summary>
        /// The main entry point for the application.
        /// </summary>
        [STAThread]
        static void Main()
        {
            Logger.SetLogfile(false);
            MasterThread.Load("Client");

            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);
            Application.Run(new frmMain());
        }
    }
}
