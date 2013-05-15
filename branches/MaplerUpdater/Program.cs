using System;
using System.Collections.Generic;
using System.Linq;
using System.Windows.Forms;

namespace MaplerUpdater
{
    static class Program
    {
        /// <summary>
        /// The main entry point for the application.
        /// </summary>
        [STAThread]
        static void Main()
        {
            AppDomain currentDomain = AppDomain.CurrentDomain;
            currentDomain.UnhandledException += new UnhandledExceptionEventHandler(UnexpectedExHandler);

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
        }
    }
}
