#!/usr/bin/env bash

if ! dpkg-query -l socat > dev/null; then
    sudo apt-get update
    sudo apt-get install socat
fi

socat TCP-LISTEN:9229,fork TCP4:127.0.0.1:9222
