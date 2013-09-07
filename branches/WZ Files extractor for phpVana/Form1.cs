using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Windows.Forms;
using MapleLib.WzLib;

namespace WZ_Files_extractor_for_phpVana
{
    public partial class Form1 : Form
    {

        public static Form1 Instance { get; private set; }
        public WzMapleVersion version = 0;
        int selected = -1;

        public Form1()
        {
            InitializeComponent();
            Instance = this;
        }

        private void Form1_Load(object sender, EventArgs e)
        {
            mapleLoc.SelectedIndex = 3;
        }

        public WzMapleVersion getMapleVersion()
        {
            switch (mapleLoc.SelectedIndex)
            {
                case 0: return WzMapleVersion.GMS;
                case 1: return WzMapleVersion.EMS;
                case 2: return WzMapleVersion.BMS;
                case 3: return WzMapleVersion.CLASSIC;
                default: return WzMapleVersion.GENERATE;
            }
        }

        private void button1_Click(object sender, EventArgs e)
        {
            button1.Enabled = false;
            version = getMapleVersion();
            chkList.Enabled = false;


            MapleFileCache.Init(wzFilesDir.Text, version);

            SqlFileItemOptions.Init();

            SQLData.Init();
            SQLStrings.Init();
            ItemInformation.Init();
            SqlFileItemSocket.Init();
            FamiliarInfoSQL.Init();

            RunNext();
        }

        private void RunNext()
        {
            if (chkList.CheckedIndices.Count == 0)
            {
                this.Invoke((MethodInvoker)delegate
                {
                    button1.Enabled = true;
                    chkList.Enabled = true;
                });

                SQLData.Instance.Dump(wzExtractionDir.Text + "\\Data.sql");

                SqlFileItemOptions.EndOfSQL();
                SqlFileItemOptions.createSQLFile(wzExtractionDir.Text + "\\Data_Item_Options.sql");

                SQLStrings.Instance.Dump(wzExtractionDir.Text + "\\Data_Strings.sql");

                ItemInformation.Instance.Finish(wzExtractionDir.Text + "\\Data_Itemdata.sql");

                SqlFileItemSocket.Instance.Dump(wzExtractionDir.Text + "\\Data_NebuliteInfo.sql");

                FamiliarInfoSQL.Instance.Dump(wzExtractionDir.Text + "\\Data_FamiliarInfo.sql");
                return;
            }

            selected = chkList.CheckedIndices[0];
            this.Invoke((MethodInvoker)delegate
            {
                chkList.SetItemCheckState(selected, CheckState.Unchecked);
            });

            if (selected == 0)
            {
                
                (new System.Threading.Thread(() =>
                {
                    new CharacterExtractor(wzExtractionDir.Text, wzFilesDir.Text, version).Start();
                    RunNext();
                })
                {
                    IsBackground = true
                }).Start();
            }
            else if (selected == 1)
            {
                (new System.Threading.Thread(() =>
                {
                    new StringsExtractor(wzExtractionDir.Text, wzFilesDir.Text, version).Start();
                    RunNext();
                })
                {
                    IsBackground = true
                }).Start();
            }
            else if (selected == 2)
            {
                (new System.Threading.Thread(() =>
                {
                    new PassiveBuffExtractor(wzExtractionDir.Text, wzFilesDir.Text, version).Start();
                    RunNext();
                })
                {
                    IsBackground = true
                }).Start();
            }
            else if (selected == 3)
            {
                (new System.Threading.Thread(() =>
                {
                    new ItemsExtractor(wzExtractionDir.Text, wzFilesDir.Text, version).Start();
                    RunNext();
                })
                {
                    IsBackground = true
                }).Start();
            }
            else if (selected == 4)
            {
                (new System.Threading.Thread(() =>
                {
                    new GuildInfoExtractor(wzExtractionDir.Text, wzFilesDir.Text, version).Start();
                    RunNext();
                })
                {
                    IsBackground = true
                }).Start();
            }
            else if (selected == 5)
            {
                (new System.Threading.Thread(() =>
                {
                    new ZMAPExporter(wzExtractionDir.Text, wzFilesDir.Text, version).Start();
                    RunNext();
                })
                {
                    IsBackground = true
                }).Start();
            }
            else if (selected == 6)
            {
                (new System.Threading.Thread(() =>
                {
                    new EtcExporter(wzExtractionDir.Text, wzFilesDir.Text, version).Start();
                    RunNext();
                })
                {
                    IsBackground = true
                }).Start();
            }
            else if (selected == 7)
            {
                (new System.Threading.Thread(() =>
                {
                    new EffectsExporter(wzExtractionDir.Text, wzFilesDir.Text, version).Start();
                    RunNext();
                })
                {
                    IsBackground = true
                }).Start();
            }
        }

        private void ExtractionDirButton_Click(object sender, EventArgs e)
        {
            FolderBrowserDialog fbd = new FolderBrowserDialog();
            if (fbd.ShowDialog() == DialogResult.OK)
                wzExtractionDir.Text = fbd.SelectedPath;
        }

        private void WzFilesDirButton_Click(object sender, EventArgs e)
        {
            FolderBrowserDialog fbd = new FolderBrowserDialog();
            if (fbd.ShowDialog() == DialogResult.OK)
                wzFilesDir.Text = fbd.SelectedPath;
        }

        private void chkList_SelectedIndexChanged(object sender, EventArgs e)
        {

        }


    }
}
