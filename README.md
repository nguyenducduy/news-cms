# PHP Extension required

  - Yaml (libyaml-devel re2c)
  - Memcached
  - Phalcon

# Useful Atom package

  * prettier-atom
  * editorconfig
  * file-icons
  * highlight-selected
  * project-manager
  * sublime-style-column-selection
  * tab-foldername-index
  * autoclose-html
  * docblockr
  * es6-javascript
  * pigments
  * tree-view-copy-relative-path
  * php-cs-fixer

# Run PM2 with Cross-ENV

  - pm2 start --name event yarn -- start-staging

# Compatible

  - Node v8.0.4
  - Yarn v0.24.6

# Install Sphinx search on Centos 6.x

  - sudo yum install postgresql-devel unixODBC
  - sudo rpm -Uvh sphinx-2.3.2-1.rhel6.x86_64.rpm
  - sudo service searchd start

# Deploy steps

  - Using Jenkins + Github

# Favicon generator

  - https://realfavicongenerator.net/

# Supervisord commands

  - supervisorctl reread
  - supervisorctl update
  - supervisorctl reload
  - supervisorctl restart all
  - supervisorctl restart worker-download:* (numprocs > 1)

# Resize existed partition in VM
  [root@music-origin ~]# fdisk /dev/xvdb

  WARNING: DOS-compatible mode is deprecated. It's strongly recommended to
         switch off the mode (command 'c') and change display units to
         sectors (command 'u').

  Command (m for help): d
  Selected partition 1

  Command (m for help): n
  Command action
   e   extended
   p   primary partition (1-4)
  p
  Partition number (1-4): 1
  First cylinder (1-65270, default 1):
  Using default value 1
  Last cylinder, +cylinders or +size{K,M,G} (1-65270, default 65270):
  Using default value 65270

  Command (m for help): t
  Selected partition 1
  Hex code (type L to list codes): 8e
  Changed system type of partition 1 to 8e (Linux LVM)

  Command (m for help): p

  Disk /dev/xvdb: 536.9 GB, 536870912000 bytes
  255 heads, 63 sectors/track, 65270 cylinders
  Units = cylinders of 16065 * 512 = 8225280 bytes
  Sector size (logical/physical): 512 bytes / 512 bytes
  I/O size (minimum/optimal): 512 bytes / 512 bytes
  Disk identifier: 0xae481cdb

    Device Boot      Start         End      Blocks   Id  System
  /dev/xvdb1               1       65270   524281243+  8e  Linux LVM

  Command (m for help): w
  The partition table has been altered!

  Calling ioctl() to re-read partition table.

  WARNING: Re-reading the partition table failed with error 16: Device or resource busy.
  The kernel still uses the old table. The new table will be used at
  the next reboot or after you run partprobe(8) or kpartx(8)
  Syncing disks.
  [root@music-origin ~]# reboot

  Broadcast message from root@music-origin
  (/dev/pts/0) at 9:51 ...

  The system is going down for reboot NOW!

    --------------------

  [root@music-origin ~]# resize2fs /dev/xvdb1
  resize2fs 1.41.12 (17-May-2010)
  Filesystem at /dev/xvdb1 is mounted on /data; on-line resizing required
  old desc_blocks = 13, new_desc_blocks = 32
  Performing an on-line resize of /dev/xvdb1 to 131070310 (4k) blocks.
  The filesystem on /dev/xvdb1 is now 131070310 blocks long.

  [root@music-origin ~]# df -h
  Filesystem            Size  Used Avail Use% Mounted on
  /dev/mapper/VolGroup-lv_root
                         37G  4.0G   32G  12% /
  tmpfs                 935M     0  935M   0% /dev/shm
  /dev/xvda1            477M  101M  351M  23% /boot
  /dev/xvdb1            493G  130G  338G  28% /data
