function urlsafe_base64_encode(content)
{
  return base64_encode(content).replace(/\+/g, '-').replace(/\//g, '_');
}

function urlsafe_base64_decode(content)
{
  return base64_decode(content.replace(/\_/g, '/').replace(/\-/g, '+'));
}

function generate_rs_put_path(tbName, fileKey, mimeType)
{
	mimeType = mimeType || 'application/octet-stream';
	entryURI = tbName + ':' + fileKey;
	return '/rs-put/' + urlsafe_base64_encode(entryURI) + '/mimeType/' + urlsafe_base64_encode(mimeType);
}
