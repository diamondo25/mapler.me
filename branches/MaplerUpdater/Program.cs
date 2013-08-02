using System;
using System.Collections.Generic;
using System.Linq;

using System.Threading;
using System.Windows.Forms;

namespace MaplerUpdater
{
    static class Program
    {
        private static Mutex _singelton = null;
        /// <summary>
        /// The main entry point for the application.
        /// </summary>
        [STAThread]
        static void Main()
        {
            AppDomain currentDomain = AppDomain.CurrentDomain;
            currentDomain.UnhandledException += new UnhandledExceptionEventHandler(UnexpectedExHandler);

            // Check if already running
            try
            {
                Mutex.OpenExisting("Mapler.me_Client");
            }
            catch
            {
                _singelton = new Mutex(true, "Mapler.me_Client");
            }

            if (_singelton == null)
            {
                MessageBox.Show("You can only run 1 instance of Mapler.me at a time.", "Mapler.me", MessageBoxButtons.OK);
                return;
            }


            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);
            Application.Run(new frmMain());
        }

        // lolname
        static void UnexpectedExHandler(object sender, UnhandledExceptionEventArgs args)
        {
            string path = Environment.CurrentDirectory + System.IO.Path.DirectorySeparatorChar + "RUNTIME_ERROR_UPDATER.txt";
            Exception e = (Exception)args.ExceptionObject;
            System.IO.File.WriteAllText(path, e.ToString());

            MessageBox.Show("An unhandled exception has been caught!\r\nPlease contact the us at support@mapler.me with the contents of the following file:\r\n\r\n" + path + "\r\n\r\nThis application will now exit.", "Mapler.me Updater Unhandled exception!!!", MessageBoxButtons.OK, MessageBoxIcon.Error);
            Console.WriteLine("Error: {0}", e.ToString());
            Environment.Exit(1);
        }
    }
}
