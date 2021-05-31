/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// start the Stimulus application
import './bootstrap';

import React from "react";
import ReactDOM from 'react-dom';
import { HydraAdmin } from "@api-platform/admin";

// Bootstrap
require("bootstrap");

// JQuery
const $ = require("jquery");
global.$ = global.jQuery = $;

const Admin = () => {
    return <HydraAdmin entrypoint="https://127.0.0.1:8000/api" />
};

if($("#admin").length !== 0)
{
    const adminElement = document.querySelector("#admin");
    ReactDOM.render(<Admin/>, adminElement);
}

require('./js/app');