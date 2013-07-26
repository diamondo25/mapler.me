using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Windows.Forms;

using System.Diagnostics;
using Microsoft.Win32;

namespace Mapler_Client
{
    public partial class frmMain : Form
    {
        public static frmMain Instance { get; private set; }

        private frmStats _statScreen = null;
        private string _mapleEXE = null;

        private KeyboardHook _keyboardHook = new KeyboardHook();

        public frmMain()
        {

            InitializeComponent();
            Instance = this;

            this.Text += Application.ProductVersion;
        }

        private void Form1_Load(object sender, EventArgs e)
        {
            try
            {
                // Get executable path
                if (Registry.LocalMachine.OpenSubKey("SOFTWARE").OpenSubKey("Wizet") != null &&
                    Registry.LocalMachine.OpenSubKey("SOFTWARE").OpenSubKey("Wizet").OpenSubKey("MapleStory") != null &&
                    Registry.LocalMachine.OpenSubKey("SOFTWARE").OpenSubKey("Wizet").OpenSubKey("MapleStory").GetValue("Executable") != null)
                {
                    _mapleEXE = (string)Registry.LocalMachine.OpenSubKey("SOFTWARE").OpenSubKey("Wizet").OpenSubKey("MapleStory").GetValue("Executable");
                }

                ServerConnection.Initialize();
                Sniffer.Init();

                _keyboardHook.KeyPressed += _keyboardHook_KeyPressed;
                _keyboardHook.RegisterHotKey(Mapler_Client.ModifierKeys.Alt, Keys.R);
            }
            catch (Exception ex)
            {
                MessageBox.Show(string.Format("Error while initializing stuff!\r\n\r\n{0}", ex.ToString()), "ERROR");
                Program.Closing = true;
                Environment.Exit(1);
            }
        }

        void _keyboardHook_KeyPressed(object sender, KeyPressedEventArgs e)
        {
            var procs = System.Diagnostics.Process.GetProcessesByName("MapleStory");
            if (procs.Length == 0 || ServerConnection.Instance == null) return;

            if (e.Modifier == Mapler_Client.ModifierKeys.Alt && e.Key == Keys.R)
            {
                Logger.WriteLine("Requesting report screenshot");
                using (MaplePacket mp = new MaplePacket(MaplePacket.CommunicationType.ClientPacket, 0xEE02))
                {
                    ServerConnection.Instance.SendPacket(mp);
                    Console.Beep(7000, 500);
                }
            }
        }

        private void frmMain_FormClosing(object sender, FormClosingEventArgs e)
        {
            Program.Closing = true;
            notifyIcon1.Visible = false;
            if (Sniffer.Instance != null)
            {
                Sniffer.Instance.Stop();
            }
            if (ServerConnection.Instance != null)
            {
                ServerConnection.Instance.Dispose();
            }
        }

        private void panel1_Click(object sender, EventArgs e)
        {
            if (_statScreen == null)
            {
                _statScreen = new frmStats();
                _statScreen.FormClosing += (a, b) =>
                {
                    _statScreen = null;
                };


                _statScreen.Show();
            }
            else
            {
                _statScreen.Focus();
            }
        }

        private void btnLaunch_Click(object sender, EventArgs e)
        {
            if (_mapleEXE == null || !System.IO.File.Exists(_mapleEXE))
            {
                if (MessageBox.Show("Couldn't find the MapleStory installation. Do you want to select MapleStory.exe yourself?", "Mapler.me", MessageBoxButtons.YesNo) == System.Windows.Forms.DialogResult.Yes)
                {
                    System.Windows.Forms.OpenFileDialog ofd = new System.Windows.Forms.OpenFileDialog();
                    ofd.FileName = "MapleStory.exe";
                    ofd.Filter = "MapleStory Executable|MapleStory.exe";
                    if (ofd.ShowDialog() == System.Windows.Forms.DialogResult.OK)
                    {
                        _mapleEXE = ofd.FileName;
                        var root = System.IO.Path.GetPathRoot(_mapleEXE);
                        Registry.SetValue("HKEY_LOCAL_MACHINE\\SOFTWARE\\Wizet\\MapleStory", "Executable", _mapleEXE);
                        Registry.SetValue("HKEY_LOCAL_MACHINE\\SOFTWARE\\Wizet\\MapleStory", "ExecPath", root);
                        Registry.SetValue("HKEY_LOCAL_MACHINE\\SOFTWARE\\Wizet\\MapleStory", "RootPath", root);
                    }
                    else
                    {
                        return;
                    }
                }
                else
                {
                    return;
                }
            }


            var procs = System.Diagnostics.Process.GetProcessesByName("MapleStory");
            if (procs.Length != 0)
            {
                if (MessageBox.Show("There seems to be one or multiple MapleStory clients running. Do you want to close these before opening another one?", "Mapler.me", MessageBoxButtons.YesNo) == System.Windows.Forms.DialogResult.Yes)
                {
                    foreach (var proc in procs)
                    {
                        try
                        {
                            proc.Kill();
                        }
                        catch (Exception ex)
                        {
                            MessageBox.Show("Unable to close! Error: " + ex.ToString());
                        }
                    }
                }
            }

            // Check MapleStory version

            if (CheckMapleEXEVersion(_mapleEXE))
            {
                System.Diagnostics.Process.Start(_mapleEXE, "WebStart");
            }
        }

        public bool CheckRunningEXEVersion()
        {
            try
            {
                var procs = System.Diagnostics.Process.GetProcessesByName("MapleStory");
                if (procs.Length > 0)
                {
                    var process = procs[0];
                    StringBuilder sb = new StringBuilder();
                    Extensions.GetModuleFileName(process.Handle, sb, 255);

                    return CheckMapleEXEVersion(sb.ToString());
                }
            }
            catch (Exception ex)
            {
                // Not allowed to
                return true;
            }

            // Not found, so I 'guess' someone is trying to haxor
            return false;
        }

        private bool CheckMapleEXEVersion(string pLocation)
        {
            FileVersionInfo fvi = FileVersionInfo.GetVersionInfo(pLocation);
            ushort msver = (ushort)fvi.ProductMinorPart;
            byte mslocale = (byte)fvi.ProductMajorPart;

            Logger.WriteLine("MapleStory v{0}.{1} locale {2}", fvi.ProductMinorPart, fvi.ProductBuildPart, fvi.ProductMajorPart);

            if (mslocale != ServerConnection.Instance.AcceptedMapleStoryLocale)
            {
                MessageBox.Show("This version of MapleStory is not supported by Mapler.me. Please contact Mapler.me at support@mapler.me if you think this is incorrect.", "Mapler.me error", MessageBoxButtons.OK, MessageBoxIcon.Exclamation);
                return false;
            }
            else if (msver > ServerConnection.Instance.AcceptedMapleStoryVersion) // Newer version?
            {
                MessageBox.Show(string.Format("It seems you are trying to start MapleStory version {0}, but Maple.me only accept version {1}! Please wait till the Mapler.me servers are updated for version {0}.", msver, ServerConnection.Instance.AcceptedMapleStoryVersion), "Mapler.me error", MessageBoxButtons.OK, MessageBoxIcon.Information);
                return false;
            }
            else if (msver < ServerConnection.Instance.AcceptedMapleStoryVersion) // Older version?
            {
                MessageBox.Show(string.Format("It seems you are trying to start MapleStory version {0}, but Maple.me only accept version {1}! Please update your client to version {1}.", msver, ServerConnection.Instance.AcceptedMapleStoryVersion), "Mapler.me error", MessageBoxButtons.OK, MessageBoxIcon.Information);
                return false;
            }

            return true;

        }

        private void label3_Click(object sender, EventArgs e)
        {

        }

        private void notifyIcon1_DoubleClick(object sender, EventArgs e)
        {
            if (this.WindowState == FormWindowState.Minimized)
            {
                this.ShowInTaskbar = true;
                this.WindowState = FormWindowState.Normal;
            }
            else
            {
                this.WindowState = FormWindowState.Minimized;
                this.ShowInTaskbar = false;
            }
        }
    }
}
