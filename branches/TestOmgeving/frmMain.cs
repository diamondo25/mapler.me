using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Windows.Forms;

namespace Mapler_Client
{
    public partial class frmMain : Form
    {
        private frmStats _statScreen = null;

        public frmMain()
        {
            InitializeComponent();
        }

        private void Form1_Load(object sender, EventArgs e)
        {
            try
            {
                ServerConnection.Initialize();
                GMSKeys.Initialize();
                Sniffer.Init();
            }
            catch (Exception ex)
            {
                MessageBox.Show(string.Format("Error while initializing stuff!\r\n\r\n{0}", ex.ToString()), "ERROR");
                Environment.Exit(1);
            }
        }

        private void frmMain_FormClosing(object sender, FormClosingEventArgs e)
        {
            Program.Closing = true;
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
    }
}
