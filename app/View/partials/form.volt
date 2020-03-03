{{ javascript_include("static/js/react.min.js") }}
{{ javascript_include("static/js/react-dom.min.js") }}
{{ javascript_include("static/js/browser.min.js") }}
{{ javascript_include("static/js/run_prettify.js") }}
{{ javascript_include("static/js/react-jsonschema-form.js") }}
{{ javascript_include("static/js/handlebars-v4.0.11.js") }}
<script type="text/babel">
const Form = JSONSchemaForm.default;

const CustomTitleField = ({id, title, required}) => {
  const legend = required ? title + '*' : title;
  return (<div className="box-header bg-info"><h5 className='box-title'>{legend}</h5></div>);
};

const fields = { TitleField: CustomTitleField };

function FTpl(props) {
  const {id, classNames, label, help, required, description, errors, children, displayLabel} = props;
  return (
    //<div className={classNames}>
    <div className={classNames || "form-group form-group-sm"}>
      {displayLabel && 
         <label className="control-label" htmlFor={id}>
           {label}
           {required && <span className="required">*</span>}
         </label>
      }
      {displayLabel && description ? description : null}
      {children}
      {errors}
      {help}
    </div>
  );
}

function transformErrors(errors)
{
  return errors.map(error => {
    if (error.name === "pattern") {
      error.message = "Only digits are allowed"
    } else if (error.name === "type") {
      error.message = "型態錯誤";
    } else if (error.name === "enum") {
      error.message = "不允許使用";
    } else {
      error.message = error.name;
    }
    return error;
  });
}

const log = (type) => console.log.bind(console, type);

window.renderForm = function() {
  ReactDOM.render((
   <Form schema={schema}
        fields={fields}
        action="/users/list"
        FieldTemplate={FTpl}
        transformErrors={transformErrors}
        uiSchema={uiSchema}
        onChange = {(data) => searchBySchema(data.formData)}
        onSubmit = {(data) => searchBySchema(data.formData)}
        onError={log("errors")} />
    ), document.getElementById("search-form"));
};

</script>
