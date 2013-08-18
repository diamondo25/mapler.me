namespace WZ_Files_extractor_for_phpVana
{
    partial class Form1
    {
        /// <summary>
        /// Required designer variable.
        /// </summary>
        private System.ComponentModel.IContainer components = null;

        /// <summary>
        /// Clean up any resources being used.
        /// </summary>
        /// <param name="disposing">true if managed resources should be disposed; otherwise, false.</param>
        protected override void Dispose(bool disposing)
        {
            if (disposing && (components != null))
            {
                components.Dispose();
            }
            base.Dispose(disposing);
        }

        #region Windows Form Designer generated code

        /// <summary>
        /// Required method for Designer support - do not modify
        /// the contents of this method with the code editor.
        /// </summary>
        private void InitializeComponent()
        {
            System.ComponentModel.ComponentResourceManager resources = new System.ComponentModel.ComponentResourceManager(typeof(Form1));
            this.progress = new System.Windows.Forms.ProgressBar();
            this.label1 = new System.Windows.Forms.Label();
            this.label2 = new System.Windows.Forms.Label();
            this.wzFilesDir = new System.Windows.Forms.TextBox();
            this.wzExtractionDir = new System.Windows.Forms.TextBox();
            this.WzFilesDirButton = new System.Windows.Forms.Button();
            this.ExtractionDirButton = new System.Windows.Forms.Button();
            this.button1 = new System.Windows.Forms.Button();
            this.label5 = new System.Windows.Forms.Label();
            this.mapleLoc = new System.Windows.Forms.ComboBox();
            this.chkList = new System.Windows.Forms.CheckedListBox();
            this.SuspendLayout();
            // 
            // progress
            // 
            resources.ApplyResources(this.progress, "progress");
            this.progress.Name = "progress";
            // 
            // label1
            // 
            resources.ApplyResources(this.label1, "label1");
            this.label1.Name = "label1";
            // 
            // label2
            // 
            resources.ApplyResources(this.label2, "label2");
            this.label2.Name = "label2";
            // 
            // wzFilesDir
            // 
            resources.ApplyResources(this.wzFilesDir, "wzFilesDir");
            this.wzFilesDir.Name = "wzFilesDir";
            // 
            // wzExtractionDir
            // 
            resources.ApplyResources(this.wzExtractionDir, "wzExtractionDir");
            this.wzExtractionDir.Name = "wzExtractionDir";
            this.wzExtractionDir.Tag = "";
            // 
            // WzFilesDirButton
            // 
            resources.ApplyResources(this.WzFilesDirButton, "WzFilesDirButton");
            this.WzFilesDirButton.Name = "WzFilesDirButton";
            this.WzFilesDirButton.UseVisualStyleBackColor = true;
            this.WzFilesDirButton.Click += new System.EventHandler(this.WzFilesDirButton_Click);
            // 
            // ExtractionDirButton
            // 
            resources.ApplyResources(this.ExtractionDirButton, "ExtractionDirButton");
            this.ExtractionDirButton.Name = "ExtractionDirButton";
            this.ExtractionDirButton.UseVisualStyleBackColor = true;
            this.ExtractionDirButton.Click += new System.EventHandler(this.ExtractionDirButton_Click);
            // 
            // button1
            // 
            resources.ApplyResources(this.button1, "button1");
            this.button1.Name = "button1";
            this.button1.UseVisualStyleBackColor = true;
            this.button1.Click += new System.EventHandler(this.button1_Click);
            // 
            // label5
            // 
            resources.ApplyResources(this.label5, "label5");
            this.label5.Name = "label5";
            // 
            // mapleLoc
            // 
            this.mapleLoc.DropDownStyle = System.Windows.Forms.ComboBoxStyle.DropDownList;
            this.mapleLoc.FormattingEnabled = true;
            this.mapleLoc.Items.AddRange(new object[] {
            resources.GetString("mapleLoc.Items"),
            resources.GetString("mapleLoc.Items1"),
            resources.GetString("mapleLoc.Items2"),
            resources.GetString("mapleLoc.Items3")});
            resources.ApplyResources(this.mapleLoc, "mapleLoc");
            this.mapleLoc.Name = "mapleLoc";
            // 
            // chkList
            // 
            this.chkList.CheckOnClick = true;
            this.chkList.FormattingEnabled = true;
            this.chkList.Items.AddRange(new object[] {
            resources.GetString("chkList.Items"),
            resources.GetString("chkList.Items1"),
            resources.GetString("chkList.Items2"),
            resources.GetString("chkList.Items3"),
            resources.GetString("chkList.Items4"),
            resources.GetString("chkList.Items5"),
            resources.GetString("chkList.Items6"),
            resources.GetString("chkList.Items7")});
            resources.ApplyResources(this.chkList, "chkList");
            this.chkList.Name = "chkList";
            this.chkList.SelectedIndexChanged += new System.EventHandler(this.chkList_SelectedIndexChanged);
            // 
            // Form1
            // 
            resources.ApplyResources(this, "$this");
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.Controls.Add(this.chkList);
            this.Controls.Add(this.mapleLoc);
            this.Controls.Add(this.label5);
            this.Controls.Add(this.button1);
            this.Controls.Add(this.ExtractionDirButton);
            this.Controls.Add(this.WzFilesDirButton);
            this.Controls.Add(this.wzExtractionDir);
            this.Controls.Add(this.wzFilesDir);
            this.Controls.Add(this.label2);
            this.Controls.Add(this.label1);
            this.Controls.Add(this.progress);
            this.FormBorderStyle = System.Windows.Forms.FormBorderStyle.FixedToolWindow;
            this.Name = "Form1";
            this.Load += new System.EventHandler(this.Form1_Load);
            this.ResumeLayout(false);
            this.PerformLayout();

        }

        #endregion

        private System.Windows.Forms.Label label1;
        private System.Windows.Forms.Label label2;
        private System.Windows.Forms.Button WzFilesDirButton;
        private System.Windows.Forms.Button ExtractionDirButton;
        private System.Windows.Forms.Label label5;
        public System.Windows.Forms.ComboBox mapleLoc;
        public System.Windows.Forms.TextBox wzFilesDir;
        public System.Windows.Forms.TextBox wzExtractionDir;
        public System.Windows.Forms.ProgressBar progress;
        public System.Windows.Forms.Button button1;
        private System.Windows.Forms.CheckedListBox chkList;
    }
}

