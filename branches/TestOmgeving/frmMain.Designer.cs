namespace  Mapler_Client
{
    partial class frmMain
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
            this.components = new System.ComponentModel.Container();
            System.ComponentModel.ComponentResourceManager resources = new System.ComponentModel.ComponentResourceManager(typeof(frmMain));
            this.panel1 = new System.Windows.Forms.Panel();
            this.label3 = new System.Windows.Forms.Label();
            this.lblLastUpdate = new System.Windows.Forms.Label();
            this.lblInfo = new System.Windows.Forms.Label();
            this.notifyIcon1 = new System.Windows.Forms.NotifyIcon(this.components);
            this.button1 = new System.Windows.Forms.Button();
            this.picStart = new System.Windows.Forms.PictureBox();
            this.lblConnecting = new System.Windows.Forms.Label();
            this.contextMenuStrip1 = new System.Windows.Forms.ContextMenuStrip(this.components);
            this.openToolStripMenuItem = new System.Windows.Forms.ToolStripMenuItem();
            this.goToMaplermeToolStripMenuItem = new System.Windows.Forms.ToolStripMenuItem();
            this.exitToolStripMenuItem = new System.Windows.Forms.ToolStripMenuItem();
            ((System.ComponentModel.ISupportInitialize)(this.picStart)).BeginInit();
            this.contextMenuStrip1.SuspendLayout();
            this.SuspendLayout();
            // 
            // panel1
            // 
            this.panel1.BackColor = System.Drawing.Color.Transparent;
            this.panel1.BackgroundImage = ((System.Drawing.Image)(resources.GetObject("panel1.BackgroundImage")));
            this.panel1.BackgroundImageLayout = System.Windows.Forms.ImageLayout.Center;
            this.panel1.Location = new System.Drawing.Point(394, 12);
            this.panel1.Margin = new System.Windows.Forms.Padding(2, 3, 2, 3);
            this.panel1.Name = "panel1";
            this.panel1.Size = new System.Drawing.Size(40, 43);
            this.panel1.TabIndex = 1;
            this.panel1.Click += new System.EventHandler(this.panel1_Click);
            // 
            // label3
            // 
            this.label3.AutoSize = true;
            this.label3.BackColor = System.Drawing.Color.Transparent;
            this.label3.Font = new System.Drawing.Font("Verdana", 8.25F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.label3.ForeColor = System.Drawing.Color.White;
            this.label3.Location = new System.Drawing.Point(8, 348);
            this.label3.Margin = new System.Windows.Forms.Padding(2, 0, 2, 0);
            this.label3.Name = "label3";
            this.label3.Size = new System.Drawing.Size(78, 13);
            this.label3.TabIndex = 4;
            this.label3.Text = "Last update:";
            this.label3.Click += new System.EventHandler(this.label3_Click);
            // 
            // lblLastUpdate
            // 
            this.lblLastUpdate.AutoSize = true;
            this.lblLastUpdate.BackColor = System.Drawing.Color.Transparent;
            this.lblLastUpdate.Font = new System.Drawing.Font("Verdana", 8.25F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.lblLastUpdate.ForeColor = System.Drawing.Color.White;
            this.lblLastUpdate.Location = new System.Drawing.Point(90, 348);
            this.lblLastUpdate.Margin = new System.Windows.Forms.Padding(2, 0, 2, 0);
            this.lblLastUpdate.Name = "lblLastUpdate";
            this.lblLastUpdate.Size = new System.Drawing.Size(166, 13);
            this.lblLastUpdate.TabIndex = 5;
            this.lblLastUpdate.Text = "(will appear once logged in)";
            // 
            // lblInfo
            // 
            this.lblInfo.AutoSize = true;
            this.lblInfo.BackColor = System.Drawing.Color.Transparent;
            this.lblInfo.ForeColor = System.Drawing.Color.White;
            this.lblInfo.Location = new System.Drawing.Point(9, 361);
            this.lblInfo.Margin = new System.Windows.Forms.Padding(2, 0, 2, 0);
            this.lblInfo.Name = "lblInfo";
            this.lblInfo.Size = new System.Drawing.Size(25, 14);
            this.lblInfo.TabIndex = 7;
            this.lblInfo.Text = "test";
            // 
            // notifyIcon1
            // 
            this.notifyIcon1.ContextMenuStrip = this.contextMenuStrip1;
            this.notifyIcon1.Icon = ((System.Drawing.Icon)(resources.GetObject("notifyIcon1.Icon")));
            this.notifyIcon1.Text = "Mapler.me Client";
            this.notifyIcon1.Visible = true;
            this.notifyIcon1.DoubleClick += new System.EventHandler(this.notifyIcon1_DoubleClick);
            // 
            // button1
            // 
            this.button1.Location = new System.Drawing.Point(359, 352);
            this.button1.Name = "button1";
            this.button1.Size = new System.Drawing.Size(75, 23);
            this.button1.TabIndex = 8;
            this.button1.Text = "Log off";
            this.button1.UseVisualStyleBackColor = true;
            this.button1.Click += new System.EventHandler(this.button1_Click);
            // 
            // picStart
            // 
            this.picStart.BackColor = System.Drawing.Color.Transparent;
            this.picStart.BackgroundImageLayout = System.Windows.Forms.ImageLayout.Center;
            this.picStart.Image = global::Mapler_Client.Properties.Resources.launch_normal;
            this.picStart.Location = new System.Drawing.Point(81, 148);
            this.picStart.Name = "picStart";
            this.picStart.Size = new System.Drawing.Size(278, 195);
            this.picStart.TabIndex = 9;
            this.picStart.TabStop = false;
            this.picStart.Click += new System.EventHandler(this.picStart_Click);
            this.picStart.MouseDown += new System.Windows.Forms.MouseEventHandler(this.picStart_MouseDown);
            this.picStart.MouseEnter += new System.EventHandler(this.picStart_MouseHover);
            this.picStart.MouseLeave += new System.EventHandler(this.ResetButton);
            this.picStart.MouseUp += new System.Windows.Forms.MouseEventHandler(this.picStart_MouseUp);
            // 
            // lblConnecting
            // 
            this.lblConnecting.BackColor = System.Drawing.Color.FromArgb(((int)(((byte)(200)))), ((int)(((byte)(0)))), ((int)(((byte)(0)))), ((int)(((byte)(0)))));
            this.lblConnecting.Dock = System.Windows.Forms.DockStyle.Fill;
            this.lblConnecting.ForeColor = System.Drawing.Color.White;
            this.lblConnecting.Location = new System.Drawing.Point(0, 0);
            this.lblConnecting.Name = "lblConnecting";
            this.lblConnecting.RightToLeft = System.Windows.Forms.RightToLeft.No;
            this.lblConnecting.Size = new System.Drawing.Size(445, 383);
            this.lblConnecting.TabIndex = 10;
            this.lblConnecting.Text = "Please wait...\r\n\r\nConnecting to server...";
            this.lblConnecting.TextAlign = System.Drawing.ContentAlignment.MiddleCenter;
            // 
            // contextMenuStrip1
            // 
            this.contextMenuStrip1.Items.AddRange(new System.Windows.Forms.ToolStripItem[] {
            this.openToolStripMenuItem,
            this.goToMaplermeToolStripMenuItem,
            this.exitToolStripMenuItem});
            this.contextMenuStrip1.Name = "contextMenuStrip1";
            this.contextMenuStrip1.Size = new System.Drawing.Size(164, 70);
            // 
            // openToolStripMenuItem
            // 
            this.openToolStripMenuItem.Name = "openToolStripMenuItem";
            this.openToolStripMenuItem.Size = new System.Drawing.Size(163, 22);
            this.openToolStripMenuItem.Text = "Open";
            this.openToolStripMenuItem.Click += new System.EventHandler(this.openToolStripMenuItem_Click);
            // 
            // goToMaplermeToolStripMenuItem
            // 
            this.goToMaplermeToolStripMenuItem.Name = "goToMaplermeToolStripMenuItem";
            this.goToMaplermeToolStripMenuItem.Size = new System.Drawing.Size(163, 22);
            this.goToMaplermeToolStripMenuItem.Text = "Go to Mapler.me";
            this.goToMaplermeToolStripMenuItem.Click += new System.EventHandler(this.goToMaplermeToolStripMenuItem_Click);
            // 
            // exitToolStripMenuItem
            // 
            this.exitToolStripMenuItem.Name = "exitToolStripMenuItem";
            this.exitToolStripMenuItem.Size = new System.Drawing.Size(163, 22);
            this.exitToolStripMenuItem.Text = "Exit";
            this.exitToolStripMenuItem.Click += new System.EventHandler(this.exitToolStripMenuItem_Click);
            // 
            // frmMain
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(6F, 14F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.BackgroundImage = global::Mapler_Client.Properties.Resources.background_craftypixel;
            this.BackgroundImageLayout = System.Windows.Forms.ImageLayout.None;
            this.ClientSize = new System.Drawing.Size(445, 383);
            this.Controls.Add(this.lblConnecting);
            this.Controls.Add(this.picStart);
            this.Controls.Add(this.button1);
            this.Controls.Add(this.lblInfo);
            this.Controls.Add(this.lblLastUpdate);
            this.Controls.Add(this.label3);
            this.Controls.Add(this.panel1);
            this.Font = new System.Drawing.Font("Arial", 8.25F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.FormBorderStyle = System.Windows.Forms.FormBorderStyle.FixedSingle;
            this.Icon = ((System.Drawing.Icon)(resources.GetObject("$this.Icon")));
            this.Margin = new System.Windows.Forms.Padding(2, 3, 2, 3);
            this.MaximizeBox = false;
            this.Name = "frmMain";
            this.StartPosition = System.Windows.Forms.FormStartPosition.CenterScreen;
            this.Text = "Mapler.me - v";
            this.TransparencyKey = System.Drawing.Color.DarkRed;
            this.FormClosing += new System.Windows.Forms.FormClosingEventHandler(this.frmMain_FormClosing);
            this.Load += new System.EventHandler(this.Form1_Load);
            this.Shown += new System.EventHandler(this.frmMain_Shown);
            ((System.ComponentModel.ISupportInitialize)(this.picStart)).EndInit();
            this.contextMenuStrip1.ResumeLayout(false);
            this.ResumeLayout(false);
            this.PerformLayout();

        }

        #endregion

        private System.Windows.Forms.Panel panel1;
        private System.Windows.Forms.Label label3;
        public System.Windows.Forms.Label lblLastUpdate;
        public System.Windows.Forms.Label lblInfo;
        private System.Windows.Forms.NotifyIcon notifyIcon1;
        private System.Windows.Forms.Button button1;
        private System.Windows.Forms.PictureBox picStart;
        private System.Windows.Forms.Label lblConnecting;
        private System.Windows.Forms.ContextMenuStrip contextMenuStrip1;
        private System.Windows.Forms.ToolStripMenuItem openToolStripMenuItem;
        private System.Windows.Forms.ToolStripMenuItem goToMaplermeToolStripMenuItem;
        private System.Windows.Forms.ToolStripMenuItem exitToolStripMenuItem;
    }
}

