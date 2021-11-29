<?php
$this->includeAtTemplateBase('includes/header_campus.php');
?>

    <form id="userpass" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post" name="f">
        <p>
            <label for="username">Username</label>
            <br>
            <span>
                <input id="username" type="text" name="username">
            </span>
        </p>
        <p>
            <label for="password">Password</label>
            <br>
            <span>
                <input id="password" type="password" name="password" autocomplete="current-password">
            </span>
        </p>
        <div id="userpass_login_button" class="row">
            <p id="submit" class="btn-wrap">
                <button id="submit_button" class="btn btn-primary" type="submit">
                    <span>
                        Log in
                    </span>
                </button>
            </p>
        </div>
        <input type="hidden" id="authstate" name="authstate" value="<?php echo $this->data['authstate']?>" />
        <input type="hidden" id="source" name="source" value="ecp-auth" />
    </form>

    <form id="external" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post" name="g">
        <button id="google" class="btn btn-block social" type="submit" name="idpentityid" value="https://login.elixir-czech.org/google-idp/">
            <img alt="icon" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH4AoHABAtAurfHgAABqpJREFUWMOtV1tsnFcR/ub8l9317jprN07sJsFxEzepg4LqJhAUmrQNUaAVJSJCAkEEoiCiiBceokrQIgHiASSUB2gNKg8VCqgXJJIqrRSsJq3k0pBeQ5tSU+FcbMW39W0v/+7+55wZHry2d727rikdaR/23L7vzHz/nBkSEazGRKQFwL0ADhFRL4AuAC3l6RkAQyLyFoB+AOeIKAdAVnPwSj/FzLczc5+IZGT1VmDmPmttl4i4K2E0nGDmNmvtIyJSkv/DrLU/Zebm/4mAtXYHM1+Qj8mY+XVrbXc9LKrUQHnw00T0NBFtrhszoyGFAiQMAWvnw0wK8DxQNAqKxhpFe4KZ71dKvVE5uEig7PYdSqmz9cBFh+DxMeh/vonw4iswHwyCp9IQq0GJZribOuHduQuRvfvgbOwEJZL1SIxbaw84jnOlhgAztxHRM0R0z/JdPJ1G6Xw/Cs+cgrk+BPgRkOMASi24DmAL0RoUiSD6pSOIfeVrcD+xeWnNkpcvM/M9juPMLhJgZgXgR0qpny8Ht9evIv/k71E8dxbk+YDrLoEuhI9o/lcelyCA23071vy6D8669hpnMvPjAH6glIIq336rUurRGvCRG8j3nUTxhTOgWNM8ODOgQ0ApUPMaqJZWUDQKGF3WBECRCFRyDSTIL5GsCLtS6igz7xIRuMwMIvohAL+KZiaL4Kk/onihfymexoASCbhbt8Hdug3Oho0g3wfPTMNc/Q/0e++AR2/C3/UZJE48Cqf91kaCjCulHmbmr7rW2hbP875R7SQLfeUiCqf/CoonFsFVewdiDxxG9P4vQ61dV3Nq6eUXEb72KuLf/A5UY3AAcIhor7W2yy2n1yrJSjgFmXwMXtcszMhaiNZw2tcj/u3vI/rFBxueGtl/AJH9B1aV2okoQUSHFREdqpktDkHFz6DpwQn4O9NQzT4i931hRfCPYHEiuttVSvVWf3NFSOYSYADVVkTswHX4O7vg7TtY5x0BrqUZmQKgaGW01jhhQ2vVIqWU6nGVUl3VBEJI7l1AAbAEihv4W1rgdPfUHMoCnHnd4PINhuusTODOTgfHD3rLw9DhVjyp5WsZSDi58AeAA7jr67+kAEamBYM3LXx3ZRd4DgB4y4cT7qoEs8KcUoCjaHnCqwmJtvX3uyIyQ0S3LKG5IL+tXEkQXDEgPd6QgKsAzyX4TtULA5b5nLVgUa/u9pwrIkNVBJQPSuwAMeA6jEGTRH86gc/PDmN7alP1x6yA4wd9fGufVHnJdwkn/lzETH4pC25oVbUaYh51jTFv+b6/e4lAFGrNZ8EKeCnswG9yPRjN5EHDA9ie+npNaDa2Uk2Q3h1hhKb6a/nUphoC1lp7RRlj+msyWmQzTse/i5/MfhLXbDOK1uD5qxfwtxuvfKheLANPnNcIQqnyyOe2u8tfxcAYM6CMMecAFCsnfb8N6zofwpR1oSBwSGEimMLJt5/E0x+80BB8LEjjkWdLeP+mLL5BloEHeh0kozW1aN4Yc5pyuRw5jvN4NBo9VrlgtpTFY+/8Cafefw4Jr6lCdC66U53Yv2E3elq2IOJGMFGYwj/GLuPi2JuYm+lAdPwYlGmDCGNtkvCH78WQrC6UrNb6TKlUOkJhGCKfz3elUqmh5Tcazo7iF6/9DgOjbyDmRitiT3CUA4fUYjVlxcLKvOyVSSE6fhwxvQ0nj8Zwx601RclsLpc75Pv+JeU4DjzPGw6C4GfLCWxKduDHu4/h3o17ULCligQkMGxQsiFKNkTIehEcALQzDaezD786arG9o0Z8JgzDv4jIJc/z5iuisheak8nkedd171q+Ixvm8dS/n8epweeQ0wEUqcYiFIv7Nu7Bid6HsL5pbU3ytNYOZjKZ3ng8XvB9f56AiKBYLKJYLHanUqkBIlpXt6IM0jh77SW8OPwqrmdvwrIFQBAI2ppuwZ72nTiy5RDuaLmtUROUnp2d3R+JRN6LxWIgIlT2Ashms5iamrqLmcc+rNYPrZbR/KSMZEclrwuraA14cnp6ek8mk4Expn5jYozB3Nwc0un0Dq312yLCH0NforXW/0qn0z1zc3PQWoOZG3dGxhhkMhlMTk6mgiD4LTPPiYj5CMDGWjsTBMETExMTsWw2W3Xzup1RRY5EGIYIggAAdiUSiYc9z9tbLqPimK8W6mtQJBCRvNb67/l8/pfMfCkej8P3fThObdFAjdrzBW+UxQkAXZFI5LDv+3d7ntejlOogokR5bY6ZR7XWV8IwHCiVSqdF5GosFkM0GoXruvOCq2P/BRNAUzONssv5AAAAAElFTkSuQmCC">
            <strong>Google</strong>
        </button>

        <input type="hidden" id="authstate" name="authstate" value="<?php echo $this->data['authstate']?>" />
        <input type="hidden" id="source" name="source" value="default-sp" />
    </form>

<?php $this->includeAtTemplateBase('includes/footer_campus.php');
