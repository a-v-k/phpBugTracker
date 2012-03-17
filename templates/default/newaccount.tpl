
<h2>{$txtTitle}</h2>

<form action="{$formAction}" method="post">
    <input type="hidden" name="createaccount" value="1">
    <table border="0">
        {if !empty($error)}
            <tr>
                <td class="error" colspan="2">{$error}</td>
            </tr>
        {/if}
        {if !$EMAIL_IS_LOGIN}
            <tr>
                <td align="right">{$txtLogin}: </td>
                <td>
                    <input type="text" name="login" value="{$login}">
                </td>
            </tr>
        {/if}
        <tr>
            <td align="right">{$txtEmail}: </td>
            <td><input type="text" name="email" value="{$email}"></td>
        </tr>
        <tr>
            <td align="right">{$txtFirstName} ({$txtOptional}):</td>
            <td>
                <input type="text" name="firstname" value="{$firstname}">
            </td>
        </tr>
        <tr>
            <td align="right">{$txtLastName} ({$txtOptional}):</td>
            <td>
                <input type="text" name="lastname" value="{$lastname}">
            </td>
        </tr>
    </table>
    <input type="submit" value="{$txtCreateNewAccount}">
</form>
