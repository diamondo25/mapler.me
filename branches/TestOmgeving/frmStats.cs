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
    public partial class frmStats : Form
    {
        public frmStats()
        {
            InitializeComponent();
        }

        private void frmStats_Load(object sender, EventArgs e)
        {

        }

        private static string GetHumanReadableAmount(int pBytes)
        {
            float val = pBytes;
            string name = "B";
            if (val > 1024)
            {
                name = "KB";
                val /= 1024;
            }
            if (val > 1024)
            {
                name = "MB";
                val /= 1024;
            }
            if (val > 1024)
            {
                name = "GB";
                val /= 1024;
            }
            if (val > 1024)
            {
                name = "TB"; // Really?
                val /= 1024;
            }
            return val.ToString("#,0.00") + " " + name;
        }

        private void tmUpdateLabels_Tick(object sender, EventArgs e)
        {
            lblReceivedData.Text = GetHumanReadableAmount(Program.RECV_DATA);
            lblSentData.Text = GetHumanReadableAmount(Program.SENT_DATA);
            lblReceivedPackets.Text = Program.RECV_PACKETS.ToString("#,0");
            lblSentPackets.Text = Program.SENT_PACKETS.ToString("#,0");
        }
    }
}
