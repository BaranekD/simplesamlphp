<?php
$this->includeAtTemplateBase('includes/header.php');
?>

    <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post" name="f">
        <table>
            <tr>
                <td rowspan="2" class="loginicon">
                    <img alt=""
                         src="/<?php echo $this->data['baseurlpath']; ?>resources/icons/experience/gtk-dialog-authentication.48x48.png" />
                </td>
                <td><label for="username">username</label></td>
                <td>
                    <input id="username" type="text" name="username" value="" />
                </td>
            </tr>
            <tr>
                <td><label for="password">password</label></td>
                <td><input id="password" type="password" tabindex="2" name="password" autocomplete="current-password" /></td>
            </tr>
            <tr id="submit">
                <td class="loginicon"></td><td></td>
                <td>
                    <button id="submit_button" class="btn" tabindex="6" type="submit">
                        Login
                    </button>
                </td>
            </tr>
        </table>
        <input type="hidden" id="authstate" name="authstate" value="<?php echo $this->data['authstate']?>" />
        <input type="hidden" id="source" name="source" value="example-userpass" />
    </form>

    <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post" name="g">
        <select id="dropdown-list" name="idpentityid">
            <option value="https://login.elixir-czech.org/google-idp/">Google</option>
        </select>
        <button class="btn" type="submit">Select</button>

        <input type="hidden" id="authstate" name="authstate" value="<?php echo $this->data['authstate']?>" />
        <input type="hidden" id="source" name="source" value="default-sp" />
    </form>

<?php $this->includeAtTemplateBase('includes/footer.php');
