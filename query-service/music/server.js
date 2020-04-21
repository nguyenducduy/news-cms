String.prototype.replaceAll = function(search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, 'g'), replacement);
};

// Load environment configuration with dotenv
var results = require('dotenv').config({
  path: `.env.${process.env.NODE_ENV}`
})
if (results.error) {
  throw results.error
}

var envConfig = results.parsed

var grpc = require('grpc')
var rr = require('rr')
var songsProto = grpc.load('protos/songs.proto')
var querystring = require('querystring')
var axios = require('axios')
var https = require('https')

var staticServers = [
  'https://cache01-music.olli.vn',
  'https://cache02-music.olli.vn'
]

const service = axios.create({
  baseURL: envConfig.MUSIC_URI,
  timeout: 300,
  httpsAgent: new https.Agent({
    rejectUnauthorized: false
  }),
  withCredentials: true
})

function setListenCount(songID, value){
  return service({
    url: `/v1/songs/${songID}/field`,
    method: 'put',
    data: {'field': 'countlisten',
      'value': value
    },
    headers: {
      Authorization: 'Bearer ' + envConfig.MUSIC_TOKEN
    }
  })
}

var server = new grpc.Server()

server.addService(songsProto.olliplaymusic.MusicService.service, {
  querySongs: function(call, callback) {
    var songs = []
    var sqlString = 'SELECT * FROM olli_songs'
    // var orderString = 'ORDER BY countlisten DESC'
    var orderString = ''
    var whereString = 'MATCH(\''

    if (call.request.name.length > 0) {
      whereString += '@name "' + call.request.name + '"/1'
    }

    if (call.request.artist.length > 0) {
      whereString += ' @artist ' + call.request.artist.replaceAll(' ', '|')
    }

    if (call.request.name.length === 0 && call.request.artist.length > 0) {
      orderString = 'ORDER BY RAND()'
    }

    whereString += '\')'

    sqlString += ' WHERE ' + whereString + ' '+ orderString +' LIMIT 5 OPTION ranker=sph04, max_matches=5'

    var mysql = require('promise-mysql')
    var connection
    mysql
      .createConnection({
        host: envConfig.SPHINX_HOST,
        port: envConfig.SPHINX_PORT
      })
      .then(function(conn) {
        connection = conn
        return connection.query(sqlString)
      })
      .then(function(rows) {
        if (rows.length > 0) {
          var selectedPath = rr(staticServers) + '/upload/songs/'
          for (var i = 0, len = rows.length; i < len; i++) {
            var song = rows[i]
            var staticPath = selectedPath + song.filepath
            songs.push({
              sid: song.sid,
              name: song.name,
              artist: song.artist,
              title: song.title,
              myid: song.myid,
              filepath: staticPath,
              album: song.album,
              genre: song.genre,
              length: song.length,
              cbr: song.cbr,
              status: song.status,
              countlisten : song.countlisten
            })
          }

          // Increase countlisten
          var newCountListen = Number(rows[0].countlisten) + 1;
          setListenCount(rows[0].id, newCountListen.toString())
        }

        connection.end()
        callback(null, songs)
      })
  }
})

server.bind('0.0.0.0:' + envConfig.QUERY_SERVICE_PORT, grpc.ServerCredentials.createInsecure())

console.log('Server running at http://0.0.0.0:' + envConfig.QUERY_SERVICE_PORT)

server.start()
