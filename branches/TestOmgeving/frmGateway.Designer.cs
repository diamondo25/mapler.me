namespace Mapler_Client
{
    partial class frmGateway
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
            this.dotGMS = new System.Windows.Forms.Panel();
            this.tooltipGMS = new System.Windows.Forms.Panel();
            this.label1 = new System.Windows.Forms.Label();
            this.tooltipEMS = new System.Windows.Forms.Panel();
            this.label2 = new System.Windows.Forms.Label();
            this.dotEMS = new System.Windows.Forms.Panel();
            this.tooltipKMS = new System.Windows.Forms.Panel();
            this.label3 = new System.Windows.Forms.Label();
            this.dotKMS = new System.Windows.Forms.Panel();
            this.label4 = new System.Windows.Forms.Label();
            this.tooltipGMS.SuspendLayout();
            this.tooltipEMS.SuspendLayout();
            this.tooltipKMS.SuspendLayout();
            this.SuspendLayout();
            // 
            // dotGMS
            // 
            this.dotGMS.BackColor = System.Drawing.Color.Transparent;
            this.dotGMS.BackgroundImage = global::Mapler_Client.Properties.Resources.gateway_dot;
            this.dotGMS.BackgroundImageLayout = System.Windows.Forms.ImageLayout.Center;
            this.dotGMS.Cursor = System.Windows.Forms.Cursors.Hand;
            this.dotGMS.Location = new System.Drawing.Point(180, 182);
            this.dotGMS.Name = "dotGMS";
            this.dotGMS.Size = new System.Drawing.Size(45, 37);
            this.dotGMS.TabIndex = 0;
            this.dotGMS.DoubleClick += new System.EventHandler(this.dotGMS_DoubleClick);
            // 
            // tooltipGMS
            // 
            this.tooltipGMS.BackColor = System.Drawing.Color.Transparent;
            this.tooltipGMS.BackgroundImage = global::Mapler_Client.Properties.Resources.gateway_hover;
            this.tooltipGMS.BackgroundImageLayout = System.Windows.Forms.ImageLayout.Center;
            this.tooltipGMS.Controls.Add(this.label1);
            this.tooltipGMS.Cursor = System.Windows.Forms.Cursors.Hand;
            this.tooltipGMS.Location = new System.Drawing.Point(136, 218);
            this.tooltipGMS.Name = "tooltipGMS";
            this.tooltipGMS.Size = new System.Drawing.Size(128, 81);
            this.tooltipGMS.TabIndex = 1;
            this.tooltipGMS.DoubleClick += new System.EventHandler(this.dotGMS_DoubleClick);
            // 
            // label1
            // 
            this.label1.AutoSize = true;
            this.label1.Font = new System.Drawing.Font("Verdana", 11.25F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.label1.Location = new System.Drawing.Point(36, 38);
            this.label1.Name = "label1";
            this.label1.Size = new System.Drawing.Size(54, 18);
            this.label1.TabIndex = 0;
            this.label1.Text = "Global";
            this.label1.DoubleClick += new System.EventHandler(this.dotGMS_DoubleClick);
            // 
            // tooltipEMS
            // 
            this.tooltipEMS.BackColor = System.Drawing.Color.Transparent;
            this.tooltipEMS.BackgroundImage = global::Mapler_Client.Properties.Resources.gateway_hover;
            this.tooltipEMS.BackgroundImageLayout = System.Windows.Forms.ImageLayout.Center;
            this.tooltipEMS.Controls.Add(this.label2);
            this.tooltipEMS.Cursor = System.Windows.Forms.Cursors.Hand;
            this.tooltipEMS.Location = new System.Drawing.Point(421, 170);
            this.tooltipEMS.Name = "tooltipEMS";
            this.tooltipEMS.Size = new System.Drawing.Size(128, 81);
            this.tooltipEMS.TabIndex = 3;
            this.tooltipEMS.DoubleClick += new System.EventHandler(this.dotEMS_DoubleClick);
            // 
            // label2
            // 
            this.label2.AutoSize = true;
            this.label2.Font = new System.Drawing.Font("Verdana", 11.25F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.label2.Location = new System.Drawing.Point(36, 38);
            this.label2.Name = "label2";
            this.label2.Size = new System.Drawing.Size(60, 18);
            this.label2.TabIndex = 0;
            this.label2.Text = "Europe";
            this.label2.Click += new System.EventHandler(this.label2_Click);
            this.label2.DoubleClick += new System.EventHandler(this.dotEMS_DoubleClick);
            // 
            // dotEMS
            // 
            this.dotEMS.BackColor = System.Drawing.Color.Transparent;
            this.dotEMS.BackgroundImage = global::Mapler_Client.Properties.Resources.gateway_dot;
            this.dotEMS.BackgroundImageLayout = System.Windows.Forms.ImageLayout.Center;
            this.dotEMS.Cursor = System.Windows.Forms.Cursors.Hand;
            this.dotEMS.Location = new System.Drawing.Point(465, 134);
            this.dotEMS.Name = "dotEMS";
            this.dotEMS.Size = new System.Drawing.Size(45, 37);
            this.dotEMS.TabIndex = 2;
            this.dotEMS.DoubleClick += new System.EventHandler(this.dotEMS_DoubleClick);
            // 
            // tooltipKMS
            // 
            this.tooltipKMS.BackColor = System.Drawing.Color.Transparent;
            this.tooltipKMS.BackgroundImage = global::Mapler_Client.Properties.Resources.gateway_hover;
            this.tooltipKMS.BackgroundImageLayout = System.Windows.Forms.ImageLayout.Center;
            this.tooltipKMS.Controls.Add(this.label3);
            this.tooltipKMS.Cursor = System.Windows.Forms.Cursors.Hand;
            this.tooltipKMS.Location = new System.Drawing.Point(584, 292);
            this.tooltipKMS.Name = "tooltipKMS";
            this.tooltipKMS.Size = new System.Drawing.Size(128, 81);
            this.tooltipKMS.TabIndex = 5;
            this.tooltipKMS.DoubleClick += new System.EventHandler(this.dotKMS_DoubleClick);
            // 
            // label3
            // 
            this.label3.AutoSize = true;
            this.label3.Font = new System.Drawing.Font("Verdana", 11.25F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.label3.Location = new System.Drawing.Point(36, 38);
            this.label3.Name = "label3";
            this.label3.Size = new System.Drawing.Size(52, 18);
            this.label3.TabIndex = 0;
            this.label3.Text = "Korea";
            this.label3.DoubleClick += new System.EventHandler(this.dotKMS_DoubleClick);
            // 
            // dotKMS
            // 
            this.dotKMS.BackColor = System.Drawing.Color.Transparent;
            this.dotKMS.BackgroundImage = global::Mapler_Client.Properties.Resources.gateway_dot;
            this.dotKMS.BackgroundImageLayout = System.Windows.Forms.ImageLayout.Center;
            this.dotKMS.Cursor = System.Windows.Forms.Cursors.Hand;
            this.dotKMS.Location = new System.Drawing.Point(628, 256);
            this.dotKMS.Name = "dotKMS";
            this.dotKMS.Size = new System.Drawing.Size(45, 37);
            this.dotKMS.TabIndex = 4;
            this.dotKMS.DoubleClick += new System.EventHandler(this.dotKMS_DoubleClick);
            // 
            // label4
            // 
            this.label4.AutoSize = true;
            this.label4.BackColor = System.Drawing.Color.Transparent;
            this.label4.Font = new System.Drawing.Font("Verdana", 12F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.label4.Location = new System.Drawing.Point(154, 9);
            this.label4.Name = "label4";
            this.label4.Size = new System.Drawing.Size(529, 18);
            this.label4.TabIndex = 6;
            this.label4.Text = "Please double-click the MapleStory locale you are going to play";
            // 
            // frmGateway
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(6F, 13F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.BackgroundImage = global::Mapler_Client.Properties.Resources.gateway_bg;
            this.BackgroundImageLayout = System.Windows.Forms.ImageLayout.Center;
            this.ClientSize = new System.Drawing.Size(885, 565);
            this.Controls.Add(this.label4);
            this.Controls.Add(this.tooltipKMS);
            this.Controls.Add(this.tooltipEMS);
            this.Controls.Add(this.dotKMS);
            this.Controls.Add(this.tooltipGMS);
            this.Controls.Add(this.dotEMS);
            this.Controls.Add(this.dotGMS);
            this.DoubleBuffered = true;
            this.FormBorderStyle = System.Windows.Forms.FormBorderStyle.FixedToolWindow;
            this.MaximumSize = new System.Drawing.Size(901, 599);
            this.MinimumSize = new System.Drawing.Size(901, 599);
            this.Name = "frmGateway";
            this.StartPosition = System.Windows.Forms.FormStartPosition.CenterScreen;
            this.Text = "Mapler.me Gateway Screen";
            this.Load += new System.EventHandler(this.frmGateway_Load);
            this.tooltipGMS.ResumeLayout(false);
            this.tooltipGMS.PerformLayout();
            this.tooltipEMS.ResumeLayout(false);
            this.tooltipEMS.PerformLayout();
            this.tooltipKMS.ResumeLayout(false);
            this.tooltipKMS.PerformLayout();
            this.ResumeLayout(false);
            this.PerformLayout();

        }

        #endregion

        private System.Windows.Forms.Panel dotGMS;
        private System.Windows.Forms.Panel tooltipGMS;
        private System.Windows.Forms.Label label1;
        private System.Windows.Forms.Panel tooltipEMS;
        private System.Windows.Forms.Label label2;
        private System.Windows.Forms.Panel dotEMS;
        private System.Windows.Forms.Panel tooltipKMS;
        private System.Windows.Forms.Label label3;
        private System.Windows.Forms.Panel dotKMS;
        private System.Windows.Forms.Label label4;
    }
}