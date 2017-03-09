# PayPal&nbsp;– Cashless payment in plentymarkets online stores

With the plentymarkets PayPal plugin, incorporate **PayPal** and **PayPal PLUS** in your online store.

## Opening a PayPal account

You need to [open a business account with PayPal](https://www.paypal.com/de/webapps/mpp/merchant) before you can set up the payment method in plentymarkets. You will then receive information and access data, which you will need for setting up the function in plentymarkets.

## Setting up PayPal in plentymarkets

Before using the full functionality of the plugin, you first have to connect your PayPal account with your plentymarkets system.

##### Adding a PayPal account:   
1. Go to **Settings&nbsp;» Orders&nbsp;» PayPal&nbsp;» Account settings**. 
2. Click on **Add PayPal account**. 
3. Enter an email address. This address also serves as the name of the account. 
4. Enter the client ID. 
5. Enter the client secret. 
6. Click on **Add**.<br /> → The PayPal account will be added and displayed in the list.

Carry out further settings for the account.

##### Managing your PayPal account:
1. Go to **Settings&nbsp;» Orders&nbsp;» PayPal&nbsp;» Account settings**. 
2. Click on the PayPal account you want to configure.<br />→ The PayPal account will open. 
3. Carry out the settings. Pay attention to the information given in table 1. 
4. **Save** the settings.

<table>
<caption>Table 1: Carrying out the PayPal account settings</caption>
	<thead>
		<th>
			Setting
		</th>
		<th>
			Explanation
		</th>
	</thead>
	<tbody>
		<tr>
			<td>
				<b>Client settings</b>
			</td>
			<td><b>Account:</b> The email address serving as the account name.<br /> <b>Client ID:</b> Your PayPal ID.<br /><b>Client secret</b> Your PayPal secret.<br /><strong><i>Note:</i></strong> This information cannot be changed.
			</td>
		</tr>
		<tr>
			<td>
				<b>Environment</b>
			</td>
			<td>			
Choose between <b>Test environment</b> and <b>Live environment</b>.<br /> <strong><i>Important</i></strong>: Since all information concerning accounts cannot be altered, two accounts are needed; one for the test environment, one for the live environment.<br /> <b>Test environment:</b> Provide test user and test data in the <strong><a href="https://developer.paypal.com/developer/accounts/" target="_blank">PayPal Sandbox</a></strong>. The test environment is inaccessible for customers.<br /> <b>Live environment:</b> Switch to this environment to make the connected PayPal account available for payments.
			</td>
		</tr>
		<tr>
			<td>
				<b>Logo URL</b>
			</td>
			<td>
			A HTTPS URL that leads to the logo. Valid file formats are .gif, .jpg or .png. The image may not exceed a maximum size of 190 pixels in width and 60 pixels in height. PayPal cuts off images that are larger. PayPal places thelogo at the very top of the shopping cart overview.
			</td>
		</tr>
		<tr>
			<td>
				<b>Shopping cart border</b>
			</td>
			<td>
				The hexadecimal HTML code of the main colour of the shop. The colour fades to white in a frame around the shopping cart overview in the PayPal checkout user interface.
			</td>
		</tr>
		<tr>
			<td>
				<b>Time of PayPal payment collection</b>
			</td>
			<td>
				<strong>Sale (immediate debit):</strong> Collect payment immediately after the order has been completed.
			</td>
		</tr>
	</tbody>
</table>

## Managing payment methods

Discover how to offer the payment methods offered by PayPal in your online store.

### Activating PayPal PLUS

After installing the PayPal plugin and connecting your account, PayPal is automatically available as payment method. This payment method appears in the checkout among the other payment methods according to its priority.<br />Proceed as described below to activate properties. The PayPal PLUS Wall offers Germany's most used payment methods: PayPal, Debit, Credit card as well as purchase by invoice&nbsp;– even without a PayPal account. 

##### Activating PayPal PLUS:

1. Go to **Settings&nbsp;» Orders&nbsp;» PayPal&nbsp;» PayPal PLUS**. 
2. Select a Client (store). 
3. Carry out the settings. Pay attention to the information given in table 2. 
4. **Save** the settings.

<table>
<caption>Table 2: Configuring PayPal PLUS</caption>
	<thead>
		<th>
			Setting
		</th>
		<th>
			Explanation
		</th>
	</thead>
	<tbody>
		<tr>
		<td class="th" align=CENTER colspan="2">General</td>
		</tr>
		<tr>
			<td>
				<b>Active account</b>
			</td>
			<td>The PayPal account for which the settings apply. Every client on the left has to be connected with an account. It is possible to use one account for several clients.</td>
		</tr>
		<tr>
			<td>
				<b>Priority</b>
			</td>
			<td>Determines the place of PayPal in the checkout as long as PayPal PLUS is not active.</td>
		</tr>
		<tr>
		<td class="th" align=CENTER colspan="2">PayPal Plus</td>
		</tr>
		<tr>
			<td>
				<b>Activate</b>
			</td>
			<td>
				Activate the PayPal PLUS Wall in the checkout. <a href="#10."><strong>Connect</strong></a> at least one container to use the PayPal PLUS Wall. 			</td>
		</tr>
		<tr>
		<td class="th" align=CENTER colspan="2">Language settings</td>
		</tr>
		<tr>
			<td>
				<b>Language</b>
			</td>
			<td>
				Set up language packages for all the languages the shop is available in. PayPal uses these packages when a customer changes the language of the online store.
			</td>
		</tr>
		<tr>
			<td>
				<b>Info page</b>
			</td>
			<td>
				Select a category page of the type <strong>content</strong> or enter the URL of a website to provide information about the payment method.
			</td>
		</tr>
		<tr>
			<td>
				<b>Display name</b>
			</td>
			<td>
				The description for the PayPal payment method in the checkout.
			</td>
		</tr>
		<tr>
			<td>
				<b>Logo</b>
			</td>
			<td>
			A HTTPS URL that leads to the logo. Valid file formats are .gif, .jpg or .png. The image may not exceed a maximum size of 190 pixels in width and 60 pixels in height. PayPal cuts off images that are larger. PayPal places thelogo at the very top of the shopping cart overview.
			</td>
		</tr>
		<tr>
		<td class="th" align=CENTER colspan="2">Additional settings</td>
		</tr>
		<tr>
			<td>
				<b>Countries of delivery</b>
			</td>
			<td>
				The PayPal payment method is active only for the countries in this list.
			</td>
		</tr>
	</tbody>
</table>

## Linking template containers

You have multiple options to integrate PayPal PLUS and the PayPal Express Checkout button in your online store. For this purpose, the plentymarkets system offers containers at relevant places which can be filled with content to meet your needs.

##### Linking the PayPal PLUS Wall:

1. Click on **Start&nbsp;» Plugins**. 
2. Click on the **Content** tab. 
3. Select the area PayPal PLUS Wall. 
4. Select one, several or all containers which shall use the PayPal PLUS Wall. Pay attention to the information given in table 3.<br /> → The content is linked to the container.

<table>
<caption>Table 3: Linking containers</caption>
	<thead>
		<th>
			Link
		</th>
		<th>
			Explanation
		</th>
	</thead>
	<tbody>
		<tr>
		<td class="th" align=CENTER colspan="2">General</td>
		</tr>
		<tr>
			<td>
				<b>PayPal Express Checkout button</b>
			</td>
			<td>Optional: Link the PayPal Express Checkout button where it is needed, e.g. on the item page (Single item) or next to the shopping cart. In clicking this button, the customer can purchase the item or the content of the shopping cart at once, without going through the regular checkout. In this case, the customer will be forwarded to the PayPal payment process. The shipping address is provided by PayPal.</td>
		</tr>
		<tr>
		<tr>
		<td class="th" align=CENTER colspan="2">PayPal Plus</td>
		</tr>
		<tr>
			<td>
				<b>PayPal PLUS Wall</b>
			</td>
			<td>
				A link to the container <strong>Checkout: Override payment method</strong> replaces all previous payment methods with the PayPal PLUS Wall. Any payment methods offered in addition to those inside the Wall&nbsp;– PayPal, Debit, Credit card and purchase by invoice&nbsp;– will be displayed below these four inside the wall in order of their priority. <a id="10." name="10."></a>
			</td>
		</tr>
	</tbody>
</table>

## License

This project is licensed under the GNU AFFERO GENERAL PUBLIC LICENSE. – find further information in the [LICENSE.md](https://github.com/plentymarkets/plugin-payment-paypal/blob/master/LICENSE.md).