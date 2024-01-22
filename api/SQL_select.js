const express = require('express');
const { Connection, Request, TYPES } = require('tedious');
const axios = require('axios');
const bodyParser = require('body-parser');
const cors = require('cors');
const app = express();
const port = 3000;

app.use(cors());
app.use(bodyParser.json());

app.post('/query', (req, res) => {
  const { query, param } = req.body;

  axios.get('http://localhost/sqldb.txt')
    .then(response => {
      const SQLtxt = response.data;
      const items = SQLtxt.split(';');

      const config = {
        server: items[0].trim(),
        authentication: {
          type: 'default',
          options: {
            userName: items[2].trim(),
            password: Buffer.from(items[3].trim(), 'base64').toString('utf-8')
          }
        },
        options: {
          database: 'DPD_DB',
          trustedconnection: false,
          encrypt: false,
          connectionTimeout: 30000,
          requestTimeout: 120000
        }
      };

      const connection = new Connection(config);

      connection.on('connect', function (err) {
        if (err) {
          console.error(err);
          return;
        }

        console.log("Connected");
        executeStatement();
      });

      connection.connect();

      function executeStatement() {
        const request = new Request(query, (err) => {
          if (err) {
            console.log(err);
          }
        });

        request.addParameter('param', TYPES.VarChar, param);

        let result = "";
        request.on('row', function (columns) {
          columns.forEach(function (column) {
            if (column.value === null) {
              console.log('NULL');
            } else {
              result += column.value + " ";
            }
          });
          console.log(result);
          result = "";
        });

        request.on('done', function (rowCount, more) {
          console.log(rowCount + ' rows returned');
        });

        request.on("requestCompleted", (rowCount, more) => {
          connection.close();
          res.json({ result: 'Query executed successfully' });
        });

        connection.execSql(request);
      }
    })
    .catch(error => {
      console.error('Error fetching the file:', error);
      res.status(500).json({ error: 'Internal Server Error' });
    });
});

app.listen(port, () => {
  console.log(`Server is running at http://localhost:${port}`);
});