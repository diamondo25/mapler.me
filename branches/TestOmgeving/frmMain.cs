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

        private KeyboardHook _keyboardHook;

        public frmMain()
        {

            InitializeComponent();
            Instance = this;

            this.Text += Application.ProductVersion;
        }

        private void Form1_Load(object sender, EventArgs e)
        {
            lblInfo.Text = "";


            if (new frmGateway().ShowDialog() != System.Windows.Forms.DialogResult.OK)
            {
                this.Close();
                return;
            }

            if (new frmLogin().ShowDialog() != System.Windows.Forms.DialogResult.OK)
            {
                this.Close();
                return;
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
                Sniffer.Instance.Stop();

            if (ServerConnection.Instance != null)
                ServerConnection.Instance.Dispose();
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
                System.Diagnostics.Process.Start(_mapleEXE, "GameLaunching");
            }
        }

        public bool CheckRunningEXEVersion()
        {
            return true; // ---
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

        private void button1_Click(object sender, EventArgs e)
        {
            this.Hide();
            frmLogin.RemoveToken();

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

            if (_keyboardHook != null)
            {
                _keyboardHook.Dispose();
                _keyboardHook = null;
            }

            if (new frmLogin().ShowDialog() != System.Windows.Forms.DialogResult.OK)
            {
                this.Close();
                return;
            }

            Form1_Load(null, null);
            this.Show();
        }

        private void picStart_MouseDown(object sender, MouseEventArgs e)
        {
            picStart.Image = global::Mapler_Client.Properties.Resources.launch_pressed;
        }

        private void ResetButton(object sender, EventArgs e)
        {
            picStart.Image = global::Mapler_Client.Properties.Resources.launch_normal;
        }

        private void picStart_Click(object sender, EventArgs e)
        {
            btnLaunch_Click(sender, e);
        }

        private void picStart_MouseUp(object sender, MouseEventArgs e)
        {
            ResetButton(sender, e);
        }

        private void picStart_MouseHover(object sender, EventArgs e)
        {
            picStart.Image = global::Mapler_Client.Properties.Resources.launch_hover;
        }

        private void frmMain_Shown(object sender, EventArgs e)
        {
            this.Refresh();

            try
            {
                // Get executable path
                if (Registry.LocalMachine.OpenSubKey("SOFTWARE").OpenSubKey("Wizet") != null &&
                    Registry.LocalMachine.OpenSubKey("SOFTWARE").OpenSubKey("Wizet").OpenSubKey("MapleStory") != null &&
                    Registry.LocalMachine.OpenSubKey("SOFTWARE").OpenSubKey("Wizet").OpenSubKey("MapleStory").GetValue("Executable") != null)
                {
                    _mapleEXE = (string)Registry.LocalMachine.OpenSubKey("SOFTWARE").OpenSubKey("Wizet").OpenSubKey("MapleStory").GetValue("Executable");
                }

                _keyboardHook = new KeyboardHook();
                _keyboardHook.KeyPressed += _keyboardHook_KeyPressed;
                _keyboardHook.RegisterHotKey(Mapler_Client.ModifierKeys.Alt, Keys.R);

                ServerConnection.Initialize(frmGateway.GatewayHostname, frmGateway.GatewayPort);
                lblConnecting.Text = "Initializing main routine...";
                Sniffer.Init();

                lblConnecting.Visible = false;
            }
            catch (Exception ex)
            {
                if (ex.ToString().Contains("Unable to connect"))
                {
                    MessageBox.Show("It looks like Mapler.me has a server check or update ongoing! Please check the website for more information.\r\n\r\nThe program will now exit.", "Mapler.me connection error!", MessageBoxButtons.OK, MessageBoxIcon.Information);
                }
                else
                {
                    MessageBox.Show(string.Format("An error occurred while initializing everything!\r\nContact us at support@mapler.me if you want more information about this error.\r\n{0}\r\n\r\nThe program will now exit.", ex.ToString()), "ERROR");
                }
                Program.Closing = true;
                Environment.Exit(1);
            }
        }

        private void openToolStripMenuItem_Click(object sender, EventArgs e)
        {
            notifyIcon1_DoubleClick(sender, e);
        }

        private void exitToolStripMenuItem_Click(object sender, EventArgs e)
        {
            this.Close();
        }

        private void goToMaplermeToolStripMenuItem_Click(object sender, EventArgs e)
        {
            Process.Start("http://www.mapler.me");
        }
    }
}
