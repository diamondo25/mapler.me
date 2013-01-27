using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using System.Drawing;
using System.Drawing.Drawing2D;
using System.Drawing.Design;
using System.Drawing.Imaging;
using System.Windows;
using System.Diagnostics;
using System.Runtime.InteropServices;

using System.Web;
using System.Net;

namespace Mapler_Client
{
    class Screenshot
    {
        [StructLayout(LayoutKind.Sequential)]
        public struct Rect
        {
            public int left;
            public int top;
            public int right;
            public int bottom;
        }

        [DllImport("user32.dll")]
        public static extern IntPtr GetWindowRect(IntPtr hWnd, ref Rect rect);
        [DllImport("user32.dll")]
        public static extern bool SetForegroundWindow(IntPtr hWnd); 

        public static bool MakeScreenshotOfMaple(string pScreenshotName)
        {
            Process[] procs = Process.GetProcessesByName("MapleStory");
            if (procs.Length == 0)
                return false;

            Process maple = procs[0];
            var rect = new Rect();
            GetWindowRect(maple.MainWindowHandle, ref rect);
            SetForegroundWindow(maple.MainWindowHandle);

            int width = rect.right - rect.left;
            int height = rect.bottom - rect.top;

            Image img = new Bitmap((int)width, (int)height);
            Graphics graph = Graphics.FromImage(img);
            graph.CopyFromScreen(rect.left, rect.top, 0, 0, new Size((int)width, (int)height));
            img.Save(pScreenshotName, ImageFormat.Jpeg);

            img.Dispose();
            graph.Dispose();

            Logger.WriteLine("Created screenshot @ {0} (Filesize: {1} bytes)", pScreenshotName, (new System.IO.FileInfo(pScreenshotName)).Length);

            return true;
        }

        public static void Upload(string pURL, int pID, string pFilename)
        {
            WebClient wc = new WebClient();
            wc.Headers.Add("Content-Type", "binary/octet-stream");
            wc.Headers.Add("X-Report-ID", pID.ToString());
            wc.UploadFileCompleted += (a, b) =>
            {
                Logger.WriteLine("File Uploaded");
            };

            wc.UploadFileAsync(new Uri(pURL), pFilename);
        }
    }
}
