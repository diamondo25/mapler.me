using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Windows.Forms;

using System.IO;
using System.Net;

namespace Mapler_Client
{
    public partial class frmLogin : Form
    {
        string _codeloc = Environment.CurrentDirectory + System.IO.Path.DirectorySeparatorChar + "code.weed";

        public frmLogin()
        {
            InitializeComponent();
        }

        private void frmLogin_Load(object sender, EventArgs e)
        {
            if (File.Exists(_codeloc))
            {
                string code = File.ReadAllText(_codeloc);
                var result = DoLoginRequest("check_code", "code=" + code);
                if (result.Item2 == "Okay")
                {
                    Program.Token = code;
                    OpenMainWindow();
                }
                else
                    MessageBox.Show(result.Item2);
            }

        }

        private void frmLogin_FormClosing(object sender, FormClosingEventArgs e)
        {
        }

        private void button2_Click(object sender, EventArgs e)
        {
            System.Diagnostics.Process.Start("http://mapler.me/register/");
        }

        private void OpenMainWindow()
        {
            File.WriteAllText(_codeloc, Program.Token);

            this.DialogResult = System.Windows.Forms.DialogResult.OK;
            this.Close();
        }


        private Tuple<string, string> DoLoginRequest(string pMode, string pData)
        {
            btnLogin.Enabled = txtPassword.Enabled = txtUsername.Enabled = false;

            string part1, part2;

            using (WebClient wc = new WebClient())
            {
                string URL = "http://mapler.me/api/login/";
                URL = "http://mplr.e.craftnet.nl/api/login/";
                URL += "?type=" + pMode;
                wc.Headers[HttpRequestHeader.ContentType] = "application/x-www-form-urlencoded";

                string result = wc.UploadString(URL, pData);
                part1 = result.Substring(0, 5);
                part2 = result.Substring(6);
            }

            btnLogin.Enabled = txtPassword.Enabled = txtUsername.Enabled = true;
            return new Tuple<string, string>(part1, part2);
        }

        private void btnLogin_Click(object sender, EventArgs e)
        {
            var result = DoLoginRequest("login", "email=" + txtUsername.Text + "&password=" + txtPassword.Text);
            if (result.Item1 == "CORRE")
            {
                Program.Token = result.Item2;
                OpenMainWindow();
            }
            else
            {
                MessageBox.Show(result.Item2);
            }
        }

        private void txtPassword_KeyPress(object sender, KeyPressEventArgs e)
        {
            if (e.KeyChar == (char)Keys.Enter)
            {
                btnLogin_Click(null, null);
            }
        }
    }
}
