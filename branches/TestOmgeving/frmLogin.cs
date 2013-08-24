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
        public static string _codeloc = Environment.CurrentDirectory + System.IO.Path.DirectorySeparatorChar + "code.weed";

        public frmLogin()
        {
            InitializeComponent();
        }

        private void frmLogin_Load(object sender, EventArgs e)
        {
            try
            {
                if (!File.Exists(_codeloc))
                {
                    File.Create(_codeloc).Close();
                    File.Delete(_codeloc);
                }
                else
                {
                    File.Open(_codeloc, FileMode.Open).Close();
                }
            }
            catch (Exception)
            {
                // Try different path
                _codeloc = Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData) + System.IO.Path.DirectorySeparatorChar + "Mapler.me" + System.IO.Path.DirectorySeparatorChar;
                if (!Directory.Exists(_codeloc))
                    Directory.CreateDirectory(_codeloc);
                _codeloc += "code.weed";
            }


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
                {
                    MessageBox.Show(result.Item2);
                    if (result.Item2.Contains("Invalid key"))
                        File.Delete(_codeloc);
                }
            }

        }

        private void frmLogin_FormClosing(object sender, FormClosingEventArgs e)
        {
            if (this.DialogResult != System.Windows.Forms.DialogResult.OK)
            {
                Environment.Exit(0);
            }
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
                URL += "?type=" + pMode;
                wc.Headers[HttpRequestHeader.ContentType] = "application/x-www-form-urlencoded";

                string result = wc.UploadString(URL, pData);
                part1 = result.Substring(0, 5);
                part2 = result.Substring(6);
            }

            btnLogin.Enabled = txtPassword.Enabled = txtUsername.Enabled = true;
            return new Tuple<string, string>(part1, part2);
        }

        public static void RemoveToken()
        {
            if (File.Exists(_codeloc))
            {
                File.Delete(_codeloc);
            }
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
