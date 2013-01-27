﻿namespace Mapler_Client
{
    partial class frmStats
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
            this.groupBox1 = new System.Windows.Forms.GroupBox();
            this.label1 = new System.Windows.Forms.Label();
            this.lblSentData = new System.Windows.Forms.Label();
            this.lblSentPackets = new System.Windows.Forms.Label();
            this.label4 = new System.Windows.Forms.Label();
            this.groupBox2 = new System.Windows.Forms.GroupBox();
            this.lblReceivedPackets = new System.Windows.Forms.Label();
            this.label6 = new System.Windows.Forms.Label();
            this.lblReceivedData = new System.Windows.Forms.Label();
            this.label8 = new System.Windows.Forms.Label();
            this.tmUpdateLabels = new System.Windows.Forms.Timer(this.components);
            this.groupBox1.SuspendLayout();
            this.groupBox2.SuspendLayout();
            this.SuspendLayout();
            // 
            // groupBox1
            // 
            this.groupBox1.Controls.Add(this.lblSentPackets);
            this.groupBox1.Controls.Add(this.label4);
            this.groupBox1.Controls.Add(this.lblSentData);
            this.groupBox1.Controls.Add(this.label1);
            this.groupBox1.Location = new System.Drawing.Point(12, 12);
            this.groupBox1.Name = "groupBox1";
            this.groupBox1.Size = new System.Drawing.Size(263, 56);
            this.groupBox1.TabIndex = 0;
            this.groupBox1.TabStop = false;
            this.groupBox1.Text = "Sent";
            // 
            // label1
            // 
            this.label1.AutoSize = true;
            this.label1.Location = new System.Drawing.Point(6, 16);
            this.label1.Name = "label1";
            this.label1.Size = new System.Drawing.Size(82, 13);
            this.label1.TabIndex = 0;
            this.label1.Text = "Amount of data:";
            // 
            // lblSentData
            // 
            this.lblSentData.AutoSize = true;
            this.lblSentData.Location = new System.Drawing.Point(145, 16);
            this.lblSentData.Name = "lblSentData";
            this.lblSentData.Size = new System.Drawing.Size(22, 13);
            this.lblSentData.TabIndex = 1;
            this.lblSentData.Text = "0 b";
            // 
            // lblSentPackets
            // 
            this.lblSentPackets.AutoSize = true;
            this.lblSentPackets.Location = new System.Drawing.Point(145, 34);
            this.lblSentPackets.Name = "lblSentPackets";
            this.lblSentPackets.Size = new System.Drawing.Size(13, 13);
            this.lblSentPackets.TabIndex = 3;
            this.lblSentPackets.Text = "0";
            // 
            // label4
            // 
            this.label4.AutoSize = true;
            this.label4.Location = new System.Drawing.Point(6, 34);
            this.label4.Name = "label4";
            this.label4.Size = new System.Drawing.Size(49, 13);
            this.label4.TabIndex = 2;
            this.label4.Text = "Packets:";
            // 
            // groupBox2
            // 
            this.groupBox2.Controls.Add(this.lblReceivedPackets);
            this.groupBox2.Controls.Add(this.label6);
            this.groupBox2.Controls.Add(this.lblReceivedData);
            this.groupBox2.Controls.Add(this.label8);
            this.groupBox2.Location = new System.Drawing.Point(12, 74);
            this.groupBox2.Name = "groupBox2";
            this.groupBox2.Size = new System.Drawing.Size(263, 56);
            this.groupBox2.TabIndex = 4;
            this.groupBox2.TabStop = false;
            this.groupBox2.Text = "Received";
            // 
            // lblReceivedPackets
            // 
            this.lblReceivedPackets.AutoSize = true;
            this.lblReceivedPackets.Location = new System.Drawing.Point(145, 34);
            this.lblReceivedPackets.Name = "lblReceivedPackets";
            this.lblReceivedPackets.Size = new System.Drawing.Size(13, 13);
            this.lblReceivedPackets.TabIndex = 3;
            this.lblReceivedPackets.Text = "0";
            // 
            // label6
            // 
            this.label6.AutoSize = true;
            this.label6.Location = new System.Drawing.Point(6, 34);
            this.label6.Name = "label6";
            this.label6.Size = new System.Drawing.Size(49, 13);
            this.label6.TabIndex = 2;
            this.label6.Text = "Packets:";
            // 
            // lblReceivedData
            // 
            this.lblReceivedData.AutoSize = true;
            this.lblReceivedData.Location = new System.Drawing.Point(145, 16);
            this.lblReceivedData.Name = "lblReceivedData";
            this.lblReceivedData.Size = new System.Drawing.Size(22, 13);
            this.lblReceivedData.TabIndex = 1;
            this.lblReceivedData.Text = "0 b";
            // 
            // label8
            // 
            this.label8.AutoSize = true;
            this.label8.Location = new System.Drawing.Point(6, 16);
            this.label8.Name = "label8";
            this.label8.Size = new System.Drawing.Size(82, 13);
            this.label8.TabIndex = 0;
            this.label8.Text = "Amount of data:";
            // 
            // tmUpdateLabels
            // 
            this.tmUpdateLabels.Enabled = true;
            this.tmUpdateLabels.Interval = 500;
            this.tmUpdateLabels.Tick += new System.EventHandler(this.tmUpdateLabels_Tick);
            // 
            // frmStats
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(6F, 13F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.ClientSize = new System.Drawing.Size(287, 143);
            this.Controls.Add(this.groupBox2);
            this.Controls.Add(this.groupBox1);
            this.FormBorderStyle = System.Windows.Forms.FormBorderStyle.FixedToolWindow;
            this.Name = "frmStats";
            this.Text = "Statistics";
            this.Load += new System.EventHandler(this.frmStats_Load);
            this.groupBox1.ResumeLayout(false);
            this.groupBox1.PerformLayout();
            this.groupBox2.ResumeLayout(false);
            this.groupBox2.PerformLayout();
            this.ResumeLayout(false);

        }

        #endregion

        private System.Windows.Forms.GroupBox groupBox1;
        private System.Windows.Forms.Label lblSentPackets;
        private System.Windows.Forms.Label label4;
        private System.Windows.Forms.Label lblSentData;
        private System.Windows.Forms.Label label1;
        private System.Windows.Forms.GroupBox groupBox2;
        private System.Windows.Forms.Label lblReceivedPackets;
        private System.Windows.Forms.Label label6;
        private System.Windows.Forms.Label lblReceivedData;
        private System.Windows.Forms.Label label8;
        private System.Windows.Forms.Timer tmUpdateLabels;
    }
}