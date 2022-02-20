#!/bin/bash

sudo mysqlpump --exclude-databases=% --users > users.sql
