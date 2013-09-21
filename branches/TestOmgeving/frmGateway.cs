using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Windows.Forms;

using System.Net;

namespace Mapler_Client
{
    public partial class frmGateway : Form
    {
        class ServerInfo
        {
            public string IP { get; set; }
            public string SniffIP { get; set; }
            public ushort Port { get; set; }
        }

        private Dictionary<int, ServerInfo> _servers = new Dictionary<int, ServerInfo>();

        public static string GatewayHostname = "";
        public static ushort GatewayPort = 0;

        public frmGateway()
        {
            InitializeComponent();
        }

        private void frmGateway_Load(object sender, EventArgs e)
        {
            WebClient wc = new WebClient();
            wc.Proxy = null;
            string responseText = wc.DownloadString("http://i.mapler.me/mapler_servers.php");
            string[] lines = responseText.Split(new string[] { "\n" }, StringSplitOptions.RemoveEmptyEntries);

            foreach (var line in lines)
            {
                string[] components = line.Split('|');
                _servers.Add(int.Parse(components[0]), new ServerInfo
                {
                    SniffIP = components[1],
                    IP = components[2],
                    Port = ushort.Parse(components[3])
                });
            }
        }

        private void TryConnect(int pLocale)
        {
            if (_servers.ContainsKey(pLocale))
            {
                GatewayHostname = _servers[pLocale].IP;
                GatewayPort = _servers[pLocale].Port;
                this.DialogResult = System.Windows.Forms.DialogResult.OK;
                this.Close();
            }
            else
            {
                MessageBox.Show("We do not support this locale at the moment!");
            }
        }

        private void dotGMS_DoubleClick(object sender, EventArgs e)
        {
            TryConnect(8);
        }

        private void dotEMS_DoubleClick(object sender, EventArgs e)
        {
            TryConnect(9);
        }

        private void dotKMS_DoubleClick(object sender, EventArgs e)
        {
            TryConnect(2);
        }

        private void label2_Click(object sender, EventArgs e)
        {

        }
    }
}
