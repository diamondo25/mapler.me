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
            AppDomain currentDomain = AppDomain.CurrentDomain;
            currentDomain.UnhandledException += new UnhandledExceptionEventHandler(UnexpectedExHandler);

            string[] args = Environment.GetCommandLineArgs();

            if (false && args.Count(a => { return a == "/updated"; }) == 0)
            {
                MessageBox.Show("Please use the MaplerUpdater.exe instead of this binary to launch the Mapler.me software.", "Mapler.me startup", MessageBoxButtons.OK, MessageBoxIcon.Exclamation);
                return;
            }

            Logger.SetLogfile(
                args.Count(a => { return a == "/packetlog"; }) > 0,
                (args.Count(a => { return a == "/debug"; }) > 0 || System.IO.File.Exists("debugmode.txt")) ? false : true // 'Disabled' 
            );

            MasterThread.Load("Client");

            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);
            Application.Run(new frmMain());
        }

        // lolname
        static void UnexpectedExHandler(object sender, UnhandledExceptionEventArgs args)
        {
            string path = Environment.CurrentDirectory + System.IO.Path.DirectorySeparatorChar + "RUNTIME_ERROR.txt";
            Exception e = (Exception)args.ExceptionObject;
            System.IO.File.WriteAllText(path, e.ToString());

            MessageBox.Show("An unhandled exception has been caught!\r\nPlease contact the us at support@mapler.me with the contents of the following file:\r\n\r\n" + path + "\r\n\r\nThis application will now exit.", "Mapler.me Unhandled exception!!!", MessageBoxButtons.OK, MessageBoxIcon.Error);
        }
    }
}
